<?php
require_once 'PHPUnit/Framework.php';

require_once JPATH_BASE.'/libraries/joomla/registry/format/xml.php';

/**
 * Test class for JRegistryFormatXML.
 * Generated by PHPUnit on 2009-10-27 at 15:13:11.
 */
class JRegistryFormatXMLTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test the JRegistryFormatXML::objectToString method.
	 */
	public function testObjectToString()
	{
		$class = new JRegistryFormatXML;
		$options = null;
		$object = new stdClass;
		$object->foo = 'bar';

		// Test basic object to string.
		$string = $class->objectToString($object, $options);
		$this->assertThat(
			$string,
			$this->equalTo("<?xml version=\"1.0\" ?>\n<config>\n	<entry name=\"foo\">bar</entry>\n</config>")
		);
	}

	/**
	 * Test the JRegistryFormatXML::stringToObject method.
	 */
	public function testStringToObject()
	{
		// This method is not implemented in the class.
	}

}
