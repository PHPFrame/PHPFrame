<?php
/**
 * PHPFrame/Database/MySQLDatabase.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Database
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since     1.0
 */

/**
 * MySQL Database class
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Database
 * @since    1.0
 */
class PHPFrame_MySQLDatabase extends PHPFrame_Database
{
	/**
     * Create PDO object to represent db connection. This class override's the 
     * parent's connect method in order to set MySQL specific attributes.
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
	protected function connect()
	{
		parent::connect();
		
        $this->getPDO()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}
	
    /**
     * Get database tables
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function getTables()
    {
        $sql = "SHOW TABLES";
        $tables = $this->fetchColumnList($sql);
            
        // Loop through every table and read structure
        foreach ($tables as $table_name) {
            // Store structure in array uning table name as key
            $sql = "SHOW COLUMNS FROM `".$table_name."`";
            $structure[$table_name] = $this->fetchAssocList($sql);
        }
    }
    
    /**
     * Get the columns of a given table
     * 
     * @param string
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getColumns($table_name)
    {
    	
    }
}
