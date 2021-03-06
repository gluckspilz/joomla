<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Checkin component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @since 1.0
 */
class CheckinViewCheckin extends JView
{
	protected $tables;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$tables = $model->checkin();

		$this->assignRef('tables', $tables);

		$this->_setToolbar();
		parent::display($tpl);
	}
	/**
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('COM_CHECKIN_GLOBAL_CHECK_IN'), 'checkin.png');
		if (JFactory::getUser()->authorise('core.admin', 'com_checkin'))
		{
			JToolBarHelper::preferences('com_checkin');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('screen.checkin','JTOOLBAR_HELP');
	}
}
