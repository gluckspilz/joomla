<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * PostgreSQL database driver
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class JDatabasePostgreSQL extends JDatabase
{
	/**
	 * The name of the database driver.
	 *
	 * @var string
	 */
	public $name = 'postgresql';

	/**
	 * The null or zero representation of a timestamp for MySQL.
	 *
	 * @var string
	 */
	protected $_nullDate = 'NULL';

	/**
	 * The character used to quote SQL statement names such as table names or
	 * field names, etc.
	 *
	 * @var string
	 */
	protected $_nameQuote = '"';

	/**
	 * PostgreSQL database driver object constructor.
	 *
	 * @see		JDatabase
	 * @throws	JException
	 * @param	array	Array of options used to configure the connection.
	 * @return	void
	 * @since	1.6
	 */
	protected function __construct($options)
	{
		// Get the options for the database driver.
		$host		= isset($options['host']) ? $options['host'] : 'localhost';
		$user		= isset($options['user']) ? $options['user'] : '';
		$password	= isset($options['password']) ? $options['password'] : '';
		$database	= isset($options['database']) ? $options['database'] : '';
		$prefix		= isset($options['prefix']) ? $options['prefix'] : 'jos_';
		$select		= isset($options['select']) ? $options['select'] : true;

		/*
		 * pg_connect() takes the port and socket as separate arguments.  Therefore, we
		 * have to extract them from the host string.
		 */
		$port	= null;
		$socket	= null;
		$target = substr(strstr($host, ':'), 1);
		if (!empty($target))
		{
			// Get the port number or socket name
			if (is_numeric($target)) {
				$port = $target;
			}
			else {
				$socket = $target;
			}

			// Extract the host name only
			$host = substr($host, 0, strlen($host) - (strlen($target) + 1));
		}

		// Build the connection string.
		$connectionString = null;
		$connectionString .= $host ? ' host='.$host : '';
		$connectionString .= $port ? ' port='.$port : '';
		$connectionString .= $database ? ' dbname='.$database : '';
		$connectionString .= $user ? ' user='.$user : '';
		$connectionString .= $password ? ' password='.$password : '';

		// Attempt to connect to the database engine.
		if (!($this->_resource = pg_connect(trim($connectionString)))) {
			throw new JException('Could not connect to PostgreSQL.', 2);
		}

		// Call parent constructor to initialize object values.
		parent::__construct($options);
	}

	/**
	 * PostgreSQL database driver object destructor.  Tidy up any residual
	 * database connection resources.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function __destruct()
	{
		if (is_resource($this->_resource)) {
			pg_close($this->_resource);
		}
	}

	/**
	 * Test to see if the PostgreSQL driver is available.
	 *
	 * @return	boolean	True on success.
	 * @since	1.5
	 */
	public static function test()
	{
		return (function_exists('pg_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return	boolean	True if connected to the database engine.
	 * @since	1.5
	 */
	public function connected()
	{
		if (is_resource($this->_resource)) {
			return pg_ping($this->_resource);
		}
		return false;
	}

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return	boolean	True if supported.
	 * @since	1.5
	 */
	public function hasUTF()
	{
		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return	boolean	True on success.
	 * @since	1.5
	 */
	public function setUTF()
	{
		return (pg_query($this->_resource, "SET NAMES 'UTF8'"));
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param	string	The string to be escaped.
	 * @param	boolean	Optional parameter to provide extra escaping.
	 * @return	string	The escaped string.
	 * @since	1.0
	 */
	public function getEscaped($text, $extra = false)
	{
		$result = pg_escape_string($this->_resource, $text);
		if ($extra) {
			$result = addcslashes($result, '%_');
		}
		return $result;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @throws	JException
	 * @return	mixed	A database cursor resource on success, boolean false on failure.
	 * @since	1.0
	 */
	public function query()
	{
		// If the database is not connected, return false.
		if (!is_resource($this->_resource)) {
			return false;
		}
		// Append the limit and offset if set.
		$sql = $this->_sql;
		if ($this->_limit > 0) {
			$sql .= ' LIMIT '.$this->_limit;
		}
		if ($this->_offset > 0) {
			$sql .= ' OFFSET '.$this->_offset;
		}

		// If debugging is enabled, log the SQL statement.
		if ($this->_debug) {
			$this->_counter++;
			$this->_log[] = $sql;
		}

		// Execute the SQL statement.
		$this->_cursor = @pg_query($this->_resource, $sql);

		// If an error occurred, throw an exception.
		if (!$this->_cursor) {
			$level = ($this->_debug ? E_ERROR : E_NOTICE);
			throw new JException(pg_last_error($this->_resource), 0, $level, $sql);
		}

		return $this->_cursor;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return	integer	The number of affected rows.
	 * @since	1.0.5
	 */
	public function getAffectedRows()
	{
		return pg_affected_rows($this->_resource);
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param	resource	An optional database cursor resource to extract the row count from.
	 * @return	integer		The number of returned rows.
	 * @since	1.0
	 */
	public function getNumRows($cursor = null)
	{
		return pg_num_rows($cursor ? $cursor : $this->_cursor);
	}

	/**
	 * Method to get the first field of the first row of the result set from
	 * the database query.
	 *
	 * @throws	JException
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadResult()
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = null;
		if ($row = pg_fetch_row($r)) {
			$result = $row[0];
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field
	 * in each row of the result set from the database query.
	 *
	 * @throws	JException
	 * @param	integer	The row offset to use to build the result array.
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadResultArray($offset = 0)
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = array();
		while ($row = pg_fetch_row($r))
		{
			$result[] = $row[$offset];
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get the first row of the result set from the database query
	 * as an associative array of ['field_name' => 'row_value'].
	 *
	 * @throws	JException
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadAssoc()
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = null;
		if ($row = pg_fetch_assoc($r)) {
			$result = $row;
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each
	 * row is an associative array of ['field_name' => 'row_value'].  The array of rows
	 * can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be generally avoided.
	 *
	 * @throws	JException
	 * @param	string	The name of a field to key the result array on.
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadAssocList($key = null)
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = array();
		while ($row = pg_fetch_assoc($r))
		{
			if ($key) {
				$result[$row[$key]] = $row;
			}
			else {
				$result[] = $row;
			}
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get the first row of the result set from the database query
	 * as an object.
	 *
	 * @throws	JException
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadObject()
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = null;
		if ($obj = pg_fetch_object($r)) {
			$result = $obj;
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each
	 * row is an object.  The array of objects can optionally be keyed by a field name, but
	 * defaults to a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be generally avoided.
	 *
	 * @throws	JException
	 * @param	string	The name of a field to key the result array on.
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadObjectList($key = null)
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = array();
		while ($obj = pg_fetch_object($r))
		{
			if ($key) {
				$result[$obj->$key] = $obj;
			}
			else {
				$result[] = $obj;
			}
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get the first row of the result set from the database query
	 * as an array.  Columns are indexed numerically so the first column in the
	 * result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @throws	JException
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadRow()
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = null;
		if ($row = pg_fetch_row($r)) {
			$result = $row;
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each
	 * row is an array.  The array of objects can optionally be keyed by a field offset, but
	 * defaults to a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field can result in unwanted
	 * behavior and should be generally avoided.
	 *
	 * @throws	JException
	 * @param	string	The offset of a field to key the result array on.
	 * @return	mixed	The return value or null if the query failed.
	 * @since	1.0
	 */
	public function loadRowList($offset = null)
	{
		// Execute the query.
		if (!($r = $this->query())) {
			return null;
		}

		// Get the return value from the result set.
		$result = array();
		while ($row = pg_fetch_row($r))
		{
			if (!is_null($offset)) {
				$result[$row[$offset]] = $row;
			}
			else {
				$result[] = $row;
			}
		}

		// Free the memory from the result set and return.
		pg_free_result($r);
		return $result;
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param	string	The name of the database table to insert into.
	 * @param	object	An object whose public properties match the table fields.
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function insertObject($table, &$object, $pk = null)
	{
		// Setup the SQL statement.
		$statement = 'INSERT INTO '.$this->nameQuote($table).' (%s) VALUES (%s)';

		// Build the fields and values arrays.
		$fields = array();
		$values = array();
		foreach (get_object_vars($object) as $k => $v)
		{
			// If the variable is internal or non-scalar or null ignore it.
			if (($k[0] == '_') || !is_scalar($v) || is_null($v)) {
				continue;
			}

			$fields[] = $this->nameQuote($k);
			$values[] = (is_string($v)) ? $this->quote($v) : $v;
		}

		// Inject fields and values then set the SQL statement.
		$this->setQuery(sprintf($statement, implode(',', $fields) , implode(',', $values)));

		// Execute the statement.
		try {
			$this->query();
		}
		catch (JException $e) {
			return false;
		}

		// Set the primary key to the object if available.
		$id = $this->insertid();
		if ($pk && $id) {
			$object->$pk = $id;
		}

		return true;
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param	string	The name of the database table to insert into.
	 * @param	object	An object whose public properties match the table fields.
	 * @param	string	The name of the primary key for the table.
	 * @param	boolean	True to update null fields or false to ignore them.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function updateObject($table, &$object, $pk, $updateNulls = true)
	{
		// Setup the SQL statement.
		$statement = 'UPDATE '.$this->nameQuote($table).' SET %s WHERE %s';

		// Build the fields array.
		$fields = array();
		foreach (get_object_vars($object) as $k => $v)
		{
			// If the variable is internal or non-scalar ignore it.
			if (($k[0] == '_') || is_object($v) || is_array($v) || is_resource($v)) {
				continue;
			}

			// Use the primary key for the WHERE clause and do not update it.
			if ($k == $pk) {
				$where = $this->nameQuote($pk).'='.(is_string($v)) ? $this->quote($v) : $v;
				continue;
			}

			// If the value is null check to see if we want to update nulls.
			if ($v === null)
			{
				// If we are updating nulls, set the value to NULL.
				if ($updateNulls) {
					$v = 'NULL';
				}
				else {
					continue;
				}
			}

			// The value is non-null, add it to the array to be updated.
			else {
				$v = (is_string($v)) ? $this->quote($v) : $v;
			}

			// Add the field to the array.
			$fields[] = $this->nameQuote($k).'='.$v;
		}

		// Inject fields and values then set the SQL statement.
		$this->setQuery(sprintf($statement, implode(',', $fields) , $where));

		// Execute the statement.
		try {
			$this->query();
		}
		catch (JException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return	integer	The value of the auto-increment field from the last inserted row.
	 * @since	1.0
	 */
	public function insertid()
	{
		$this->setQuery('SELECT lastval()');
		try {
			$id = (int) $this->loadResult();
		}
		catch (JException $e) {
			return 0;
		}

		return $id;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function startTransaction()
	{
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function rollbackTransaction()
	{
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function commitTransaction()
	{
	}

	/**
	 * Method to get the database engine version number.
	 *
	 * @return	string	The version number.
	 * @since	1.0
	 */
	public function getVersion()
	{
		$v = pg_version($this->_resource);
		return @$v['server_version'];
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a
	 * table in the database.
	 *
	 * @return	mixed	The collation in use by the database or boolean false if not supported.
	 * @since	1.5
	 */
	public function getCollation()
	{
		if ($this->hasUTF())
		{
			$this->setQuery('SHOW LC_COLLATE');

			// Execute the statement.
			try {
				$collation = $this->loadResult();
			}
			catch (JException $e) {
				return 'N/A (Not Able to Detect)';
			}

			// Return the collation value from the table field.
			return $collation;
		}
		else {
			// The database does not support collations.
			return false;
		}
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @throws	JException
	 * @return	array	An array of all the tables in the database.
	 * @since	1.0
	 */
	public function getTableList()
	{
		$this->setQuery('SELECT tablename FROM pg_tables WHERE tablename !~ "^pg_+"');
		return $this->loadResultArray();
	}

	/**
	 * Shows the CREATE TABLE statement that creates the given tables.
	 *
	 * @throws	JException
	 * @param 	mixed	A table name or a list of table names
	 * @return	array	A list the create SQL for the tables
	 * @since	1.0
	 */
	public function getTableCreate($tables)
	{
		// Ensure the tables values is an array.
		settype($tables, 'array');

		$result = array();
		foreach ($tables as $table)
		{
			// TODO
		}

		return $result;
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @throws	JException
	 * @param 	mixed	A table name or a list of table names
	 * @param	boolean	True to only return field types.
	 * @return	array	An array of fields by table.
	 * @since	1.0
	 */
	public function getTableFields($tables, $typeOnly = true)
	{
		// Ensure the tables values is an array.
		settype($tables, 'array');

		$result = array();
		foreach ($tables as $table)
		{
			// TODO
		}

		return $result;
	}
}
