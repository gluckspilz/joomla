<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_BASE.'/libraries/joomla/form/formfield.php';
require_once JPATH_BASE.'/libraries/joomla/form/fields/accesslevel.php';
/**
 * Test class for JFormFieldAccessLevels.
 * Generated by PHPUnit on 2009-11-08 at 19:34:34.
 */
class JFormFieldAccessLevelsTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var JFormFieldAccessLevels
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new JFormFieldAccessLevel;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * Empty test to prevent warnings.
	 */
	public function testNothing() {
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('This test does nothing at all.');
	}
}