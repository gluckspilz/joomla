<?php

defined('JPATH_BASE') or die();

jimport('joomla.base.adapterinstance');

class JBackupTable extends JAdapterInstance {
	
	
	/**
	 * Run a back up with a set of tables
	 * @param $options['tables'] The tables to backup; if blank all tables
	 * @param $options['prefix'] Table prefix; default is "bak_"
	 * @return bool Result of backups, true on success, false on failure
	 */
	function backup($options=Array()) {
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		// force this into an array if it isn't; lets not let someone break something
		if(!is_array($options)) $options = Array();
		// check if this is set otherwise set it to all of the tables
		if(!isset($options['tables'])) {
			$options['tables'] = Array();
			
			$tables = $db->getTableList(); // default table
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( "/^".$prefix."/i", $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		// set the default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'bak_';
		}
		
		foreach($options['tables'] as $table) {
			// if the table isn't in our table list ignore it
			if(!in_array($table, $tables)) continue;
			
			// change the #__ and the current prefix (typically jos_);
			// should probably change this to a regexp and check that its at the start of the string
			// TODO: Change the str_replace to a regex
			$baktable = $options['prefix'].str_replace('#__',$prefix,$table);
			$db->setQuery('DROP TABLE IF EXISTS '. $baktable);
			$db->Query(); // kill the table if it exists
			$db->setQuery('CREATE TABLE '. $baktable .' SELECT * FROM '. $table);
			$result = $db->Query(); // create the backup
		}
		return true;
	}
	
	
	/**
	 * Run a restore with a set of tables
	 * @param $options['tables'] The tables to restore; if blank all tables
	 * @param $options['prefix'] Table prefix of the backups
	 * @return bool Result of the restore: true on success, false on failure
	 */
	function restore($options=Array()) {
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $db->getTableList(); // grab this here so we can use it later
		// force this into an array if it isn't; lets not let someone break something
		if(!is_array($options)) $options = Array();
		// check if this is set otherwise set it to all of the tables
		if(!isset($options['tables'])) {
			$options['tables'] = Array();
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( "/^".$prefix."/i", $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		// set the default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'bak_';
		}
		
		foreach($options['tables'] as $table) {
			// TODO: Read the todo above a line that looks like this
			$baktable = $options['prefix'].str_replace('#__',$prefix,$table);
			// if the backup table exists and the original table exists...
			if(in_array($baktable, $tables) && in_array($table, $tables)) {
				// truncate the original and select the backup into it
				$db->setQuery('TRUNCATE TABLE '. $table);
				$db->Query(); // this should always work
				$db->setQuery('INSERT INTO '. $table .' SELECT * FROM '. $baktable);
				$db->Query(); // restore the backup
			}
		}
		return true;
	}
	
	/**
	 * Remove a given backup
	 * @param $options['tables'] The tables to remove backup copies of; if blank all backups are removed
	 * @param $options['prefix'] Table prefix of the backups
	 * 
	 */
	function remove($options=Array()) {
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $db->getTableList(); // grab this here so we can use it later
		// force this into an array if it isn't; lets not let someone break something
		if(!is_array($options)) $options = Array();
		// check if this is set otherwise set it to all of the tables
		if(!isset($options['tables'])) {
			$options['tables'] = Array();
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( '/^'.$prefix.'/i', $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		// set the default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'bak_';
		}
		
		foreach($options['tables'] as $table) {
			// TODO: Read the todo above a line that looks like this
			$baktable = $options['prefix'].str_replace('#__',$prefix,$table);
			$db->setQuery('DROP TABLE IF EXISTS '. $baktable);
			$db->Query();
		}
		return true;
	}
}