<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * Template for a basic unit test
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2008 Open Source Matters, Inc.
 * @version $Id$
 *
 */

/*
 * We now return to our regularly scheduled environment.
 */
require_once 'JRequest-helper-dataset.php';

/**
 * A unit test class for SubjectClass
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class JRequestTest_Clean extends PHPUnit_Framework_TestCase
{
	/**
	 * Define some sample data
	 */
	function setUp()
	{
		JRequestTest_DataSet::initSuperGlobals();
		// Make sure the request hash is clean.
		$GLOBALS['_JREQUEST'] = array();
	}

	public function loadFramework()
	{
		require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/bootstrap.php');
		require_once dirname(__FILE__) . DS . 'JFilterInput-mock-general.php';
		jimport('joomla.environment.request');
	}

	function testRequestClean()
	{
		$this->loadFramework();

		/*
		 * Call the method.
		 */
		$expect = count($_POST);
		JRequest::clean();
		$this -> assertEquals($expect, count($_POST), '_POST[0] was modified.');
	}

	function testRequestCleanWithBanned()
	{
		$this->markTestIncomplete('This test needs work.');
		$this->loadFramework();
		try {
			$passed = false;
			$_POST['_post'] = 'This is banned.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

	function testRequestCleanWithNumeric()
	{
		$this->markTestIncomplete('This test needs work.');
		$this->loadFramework();
		try {
			$passed = false;
			$_POST[0] = 'This is invalid.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

	function testRequestCleanWithNumericString()
	{
		$this->markTestIncomplete('This test needs work.');
		$this->loadFramework();
		try {
			$passed = false;
			$_POST['0'] = 'This is invalid.';
			/*
			 * Call the clean method.
			 */
			JRequest::clean();
		} catch (Exception $e) {
			$passed = true;
		}
		if (! $passed) {
			$this -> fail('JRequest::clean() didn\'t die on a banned variable.');
		}
	}

}
