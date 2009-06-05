<?php
/**
 * @version 	$Id$
 * @package		PHPFrame
 * @subpackage	database
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Row Collection Class
 * 
 * @package		PHPFrame
 * @subpackage 	database
 * @since 		1.0
 * @see 		Iterator
 */
class PHPFrame_Database_RowCollection implements Iterator {
	/**
	 * The SQL query that produced this collection
	 * 
	 * @var	string
	 */
	private $_query=null;
	/**
	 * The name of the database table where this collection's rows are stored
	 * 
	 * @var	string
	 */
	private $_table_name=null;
	/**
	 * The rows that make up the collection
	 * 
	 * @var unknown_type
	 */
	private $_rows=null;
	/**
	 * A pointer used to iterate through the rows array
	 * 
	 * @var	int
	 */
	private $_pos=0;
	
	/**
	 * Constructor
	 * 
	 * @param	string	$query	A SQL query
	 * @return	void
	 */
	public function __construct($query) {
		$this->_query = (string) $query;
		
		// Get table name from query string
		$this->_fetchTableName($query);
		
		// Fetch rows from db
		$this->_fetchRows($query);
	}
	
	/**
	 * Get rows in collection
	 * 
	 * @return	array
	 */
	public function getRows() {
		return $this->_rows;
	}
	
	/**
	 * Get total number of rows in collection
	 * 
	 * @return	int
	 */
	public function countRows() {
		return count($this->_rows);
	}
	
	/**
	 * Implementation of Iterator::current()
	 * 
	 * @return 	object of type PHPFrame_Database_Row
	 */
	public function current() {
		return $this->_rows[$this->_pos];
	}
	
	/**
	 * Implementation of Iterator::next()
	 * 
	 * @return void
	 */
	public function next() {
		$this->_pos++;
	}
	
	/**
	 * Implementation of Iterator::key()
	 * 
	 * @return	int
	 */
	public function key() {
		return $this->_pos;
	}
	
	/**
	 * Implementation of Iterator::valid()
	 * 
	 * @return	boolean
	 */
	public function valid() {
		return ($this->key() < $this->countRows());
	}
	
	/**
	 * Implementation of Iterator::rewind()
	 * 
	 * @return	void
	 */
	public function rewind() {
		$this->_pos = 0;
	}
	
	/**
	 * Fetch table name from SQL query string
	 * 
	 * @param	string	$query
	 * @return	void
	 */
	private function _fetchTableName($query) {
		// Figure out table name from query
		$pattern = '/FROM #__([a-zA-Z_]+)/';
		preg_match($pattern, $query, $matches);
		
		if (!isset($matches[1])) {
			throw new PHPFrame_Exception("Could not find collection table", 
										 PHPFrame_Exception::E_PHPFRAME_NOTICE,
										 "Regular expression failed to find table in query. 
										  Using pattern '".$pattern."' on subject: '".$query."'");
		}
		
		$this->_table_name = (string) "#__".$matches[1];
	}
	
	/**
	 * Run query and load array of row objects
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	private function _fetchRows($query) {
		// Run SQL query
		$db = PHPFrame::getDB();
		$db->setQuery($query);
		$rs = $db->query();
		
		// Check query result is valid
		if ($rs === false) return false;
		
		// Fetch associative array
		while ($array = mysql_fetch_assoc($rs)) {
			$row = new PHPFrame_Database_Row($this->_table_name);
			$this->_rows[] = $row->bind($array);
		}
	}
}
