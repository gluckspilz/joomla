<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Menu Item Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusModelItem extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context		= 'com_menus.item';

	/**
	 * Returns a reference to the a Table object, always creating it
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function &getTable($type = 'Menu', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app	= &JFactory::getApplication('administrator');
		$params	= &JComponentHelper::getParams('com_menus');

		// Load the User state.
		if (!($id = (int) $app->getUserState('com_menus.edit.item.id'))) {
			$id = (int) JRequest::getInt('item_id');
		}
		if (!($parentId = $app->getUserState('com_menus.edit.item.parent_id'))) {
			$parentId = JRequest::getInt('parent_id');
		}
		if (!($menuType = $app->getUserState('com_menus.edit.item.menutype'))) {
			$menuType = JRequest::getCmd('menutype', 'mainmenu');
		}
		if (!($type = $app->getUserState('com_menus.edit.item.type'))) {
			$type = JRequest::getCmd('type', 'url');
		}
		$this->setState('item.id',			$id);
		$this->setState('item.parent_id',	$parentId);
		$this->setState('item.menutype',	$menuType);
		$this->setState('item.type',		$type);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a menu item.
	 *
	 * @param	integer	The id of the menu item to get.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($itemId = null)
	{
		// Initialize variables.
		$itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('item.id');
		$false	= false;

		// Get a menu item row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->serError($table->getError());
			return $false;
		}

		// Prime required properties.
		if (empty($table->id))
		{
			$table->parent_id	= $this->getState('item.parent_id');
			$table->menutype	= $this->getState('item.menutype');
			$table->type		= $this->getState('item.type');
		}

		// Convert the params field to an array.
		$registry = new JRegistry();
		$registry->loadJSON($table->params);
		$table->params = $registry->toArray();

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		return $value;
	}

	/**
	 * Method to get the menu item form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
		$form = &JForm::getInstance('jform', 'item', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_menus.edit.item.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get the available menu item type options.
	 *
	 * @return	array	Array of groups with menu item types.
	 * @since	1.6
	 */
	public function getTypeOptions()
	{
		// Initialize variables.
		$list = array();

		// Get the list of components.
		$db = & JFactory::getDBO();
		$db->setQuery(
			'SELECT `name`, `option`' .
			' FROM `#__components`' .
			' WHERE `link` <> ""' .
			' AND `parent` = 0' .
			' ORDER BY `name`'
		);
		$components = $db->loadObjectList();

		foreach ($components as $component)
		{
			if ($options = $this->_getTypeOptionsByComponent($component->option)) {
				$list[$component->name] = $options;
			}
		}

		return $list;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		$id	= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('item.id');
		$isNew	= true;

		// Get a row instance.
		$table = &$this->getTable();

		// Load the row if saving an existing item.
		if ($id > 0) {
			$table->load($id);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Rebuild the hierarchy.
		if (!$table->rebuildTree()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return $table->id;
	}

	/**
	 * Method to delete groups.
	 *
	 * @param	array	An array of item ids.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($itemIds)
	{
		// Sanitize the ids.
		$itemIds = (array) $itemIds;
		JArrayHelper::toInteger($itemIds);

		// Get a group row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($itemIds as $itemId)
		{
			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Rebuild the hierarchy.
		if (!$table->rebuildTree()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}


	/**
	 * Method to publish categories.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish($itemIds, $value = 1)
	{
		// Sanitize the ids.
		$itemIds = (array) $itemIds;
		JArrayHelper::toInteger($itemIds);

		// Get the current user object.
		$user = &JFactory::getUser();

		// Get a category row instance.
		$table = &$this->getTable();

		// Attempt to publish the items.
		if (!$table->publish($itemIds, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The numeric id of the row to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 * @since	1.0
	 */
	public function ordering($id, $direction = 0)
	{
		// Sanitize the id and adjustment.
		$id	= (!empty($id)) ? $id : (int) $this->getState('item.id');

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to adjust the row ordering.
		if (!$table->ordering((int) $direction, $id)) {
			$this->setError($table->getError());
			return false;
		}

		// Rebuild the hierarchy.
		if (!$table->rebuildTree()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$id The numeric id of a row
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkin($id = null)
	{
		// Initialize variables.
		$id	= (!empty($id)) ? $id : (int) $this->getState('item.id');

		// Only attempt to check the row in if it exists.
		if ($id)
		{
			// Get a category row instance.
			$table = &$this->getTable();

			// Attempt to check the row in.
			if (!$table->checkin($id)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param	int		$id	The numeric id of the row to check-out.
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkout($id = null)
	{
		// Initialize variables.
		$id		= (!empty($id)) ? $id : (int) $this->getState('item.id');

		// Only attempt to check the row in if it exists.
		if ($id)
		{
			// Get a row instance.
			$table = &$this->getTable();

			// Get the current user object.
			$user = &JFactory::getUser();

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $id)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to perform batch operations on a category or a set of categories.
	 *
	 * @param	array	An array of commands to perform.
	 * @param	array	An array of category ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	function batch($commands, $itemIds)
	{
		// Sanitize the ids.
		$itemIds = (array) $itemIds;
		JArrayHelper::toInteger($itemIds);

		// Get the current user object.
		$user = &JFactory::getUser();

		// Get a category row instance.
		$table = &$this->getTable();

		// TODO: BUILD OUT BATCH OPERATIONS

		return true;
	}

	protected function _getTypeOptionsByComponent($component)
	{
		// Initialize variables.
		$options = array();

		$mainXML = JPATH_SITE.'/components/'.$component.'/metadata.xml';
		if (is_file($mainXML)) {
			$options = $this->_getTypeOptionsFromXML($mainXML, $component);
		}

		if (empty($options)) {
			$options = $this->_getTypeOptionsFromMVC($component);
		}

		return $options;
	}

	protected function _getTypeOptionsFromXML($file, $component)
	{
		// Initialize variables.
		$options = array();

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($file)) {
			return false;
		}

		// Look for the first menu node off of the root node.
		if (!$menu = $xml->xpath('menu[1]')) {
			return false;
		}
		else {
			$menu = $menu[0];
		}

		// If we have no options to parse, just add the base component to the list of options.
		if (!empty($menu['options']) && $menu['options'] == 'none')
		{
			// Create the menu option for the component.
			$o = new JObject;
			$o->title		= $menu['name'];
			$o->description	= $menu['msg'];
			$o->request		= array('option' => $component);

			$options[] = $o;

			return $options;
		}

		// Look for the first options node off of the menu node.
		if (!$optionsNode = $menu->xpath('options[1]')) {
			return false;
		}
		else {
			$optionsNode = $optionsNode[0];
		}

		// Make sure the options node has children.
		if (!$children = $optionsNode->children()) {
			return false;
		}
		else {
			// Process each child as an option.
			foreach ($children as $child)
			{
				if ($child->getName() == 'option')
				{
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= $child['name'];
					$o->description	= $child['msg'];
					$o->request		= array('option' => $component, (string) $optionsNode['var'] => (string) $child['value']);

					$options[] = $o;
				}
				elseif ($child->getName() == 'default')
				{
					// Create the menu option for the component.
					$o = new JObject;
					$o->title		= $child['name'];
					$o->description	= $child['msg'];
					$o->request		= array('option' => $component);

					$options[] = $o;
				}
			}
		}

		return $options;
	}

	protected function _getTypeOptionsFromMVC($component)
	{
		// Initialize variables.
		$options = array();

		// Get the views for this component.
		$path = JPATH_SITE.'/components/'.$component.'/views';
		if (JFolder::exists($path)) {
			$views = JFolder::folders($path);
		}
		else {
			return false;
		}
		foreach ($views as $view)
		{
			// Ignore private views.
			if (strpos($view, '_') !== 0)
			{
				// Determine if a metadata file exists for the view.
				$file = $path.'/'.$view.'/metadata.xml';
				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('view[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true') {
								unset($xml);
								continue;
							}

							// Do we have an options node or should we process layouts?
							// Look for the first options node off of the menu node.
							if ($optionsNode = $menu->xpath('options[1]'))
							{
								$optionsNode = $optionsNode[0];

								// Make sure the options node has children.
								if ($children = $optionsNode->children())
								{
									// Process each child as an option.
									foreach ($children as $child)
									{
										if ($child->getName() == 'option')
										{
											// Create the menu option for the component.
											$o = new JObject;
											$o->title		= $child['name'];
											$o->description	= $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view, (string) $optionsNode['var'] => (string) $child['value']);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default')
										{
											// Create the menu option for the component.
											$o = new JObject;
											$o->title		= $child['name'];
											$o->description	= $child['msg'];
											$o->request		= array('option' => $component, 'view' => $view);

											$options[] = $o;
										}
									}
								}
							}
							else {
								$options = array_merge($options, (array) $this->_getTypeOptionsFromLayouts($component, $view));
							}
						}
						unset($xml);
					}

				} else {
					$options = array_merge($options, (array) $this->_getTypeOptionsFromLayouts($component, $view));
				}
			}
		}

		return $options;
	}

	protected function _getTypeOptionsFromLayouts($component, $view)
	{
		// Initialize variables.
		$options = array();

		// Get the layouts from the view folder.
		$path = JPATH_SITE.'/components/'.$component.'/views/'.$view.'/tmpl';
		if (JFolder::exists($path)) {
			$layouts = JFolder::files($path, '.php$');
		}
		else {
			return $options;
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos($layout, '_') === false)
			{
				// Get the layout name.
				$layout = JFile::stripext($layout);

				// Create the menu option for the layout.
				$o = new JObject;
				$o->title		= ucfirst($layout);
				$o->description	= '';
				$o->request		= array('option' => $component, 'view' => $view);

				// Only add the layout request argument if not the default layout.
				if ($layout != 'default') {
					$o->request['layout'] = $layout;
				}

				// Load layout metadata if it exists.
				$file = $path.'/'.$layout.'.xml';
				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('layout[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true') {
								unset($xml);
								unset($o);
								continue;
							}

							// Populate the title and description if they exist.
							if (!empty($menu['title'])) {
								$o->title = trim((string) $menu['title']);
							}
							if (!empty($menu->message[0])) {
								$o->description = trim((string) $menu->message[0]);
							}
						}
					}
				}

				// Add the layout to the options array.
				$options[] = $o;
			}
		}

		return $options;
	}
}
