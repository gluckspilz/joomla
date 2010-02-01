<?php
/**
 * @version		$Id$
 * @package    Joomla.UnitTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Test class for JCacheStorageMemcache.
 * Generated by PHPUnit on 2009-10-08 at 21:46:10.
 *
 * @package    Joomla.UnitTest
 * @subpackage Cache
 *
 */
class JCacheStorageMemcacheTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JCacheStorageMemcache
	 * @access protected
	 */
	protected $object;

	/**
	 * @var    memcacheAvailable
	 * @access protected
	 */
	protected $memcacheAvailable;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		include_once JPATH_BASE.'/libraries/joomla/cache/storage.php';
		include_once JPATH_BASE.'/libraries/joomla/cache/storage/memcache.php';
		
		$this->memcacheAvailable = (extension_loaded('memcache') && class_exists('Memcache'));
		if ($this->memcacheAvailable)
		{
			$this->object = JCacheStorage::getInstance('memcache');
		}
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 * @access protected
	 */
	protected function tearDown()
	{
	}

	/**
	 * @return void
	 * @todo Implement testGetConnection().
	 */
	public function testGetConnection()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testGetConfig().
	 */
	public function testGetConfig()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testGet().
	 */
	public function testGet()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testStore().
	 */
	public function testStore()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testRemove().
	 */
	public function testRemove()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testClean().
	 */
	public function testClean()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement testGc().
	 */
	public function testGc()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * Testing test().
	 *
	 * @return void
	 */
	public function testTest()
	{
		if ($this->memcacheAvailable)
		{
			$this->assertThat(
				$this->object->test(),
				$this->isTrue(),
				'Claims memcache is not loaded.'
			);
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}

	/**
	 * @return void
	 * @todo Implement test_getCacheId().
	 */
	public function testGetCacheId()
	{
		if ($this->memcacheAvailable)
		{
			$this->markTestIncomplete('This test has not been implemented yet.');
		}
		else
		{
			$this->markTestSkipped('This caching method is not supported on this system.');
		}
	}
}
?>
