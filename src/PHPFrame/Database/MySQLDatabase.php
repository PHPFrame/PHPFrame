<?php
/**
 * PHPFrame/Database/MySQLDatabase.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Database
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since     1.0
 */

/**
 * This class is the MySQL implementation of {@link PHPFrame_Database}.
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_Database
 * @since    1.0
 */
class PHPFrame_MySQLDatabase extends PHPFrame_Database
{
    /**
     * Create PDO object to represent db connection. This class override's the 
     * parent's connect method in order to set MySQL specific attributes.
     * 
     * @return void
     * @since  1.0
     */
    protected function connect()
    {
        parent::connect();
        
        $this->getPDO()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }
    
    /**
     * Get the database tables.
     * 
     * @param bool $return_names [Optional] Default value is FALSE. If set to 
     *                           TRUE an array containing table names will be 
     *                           returned instead of an array containing  
     *                           objects of type {@link PHPFrame_DatabaseTable}.
     * 
     * @return array
     * @since  1.0
     */
    public function getTables($return_names=false)
    {
        $sql       = "SHOW TABLES";
        $tbl_names = $this->fetchColumnList($sql);
        $tbl_objs  = array();
        
        if ((bool) $return_names && is_array($tbl_names)) {
            return $tbl_names;
        }
        
        if (is_array($tbl_names) && count($tbl_names) > 0) {
            foreach ($tbl_names as $tbl_name) {
                $tbl_objs[] = new PHPFrame_DatabaseTable($this, $tbl_name);
            }
        }
        
        return $tbl_objs;
    }
    
    /**
     * Create database table for a given table object
     * 
     * @param PHPFrame_DatabaseTable $table A reference to an object of type 
     *                                      PHPFrame_DatabaseTable representing 
     *                                      the table we want to create.
     * 
     * @return void
     * @since  1.0
     * @todo   This method needs to be finished...
     */
    public function createTable(PHPFrame_DatabaseTable $table)
    {
        $sql = "CREATE TABLE `".$table->getName()."` (\n";
        
        foreach ($table->getColumns() as $col) {
            $sql .= "`".$col->getName()."`";
            $sql .= " ".$col->getType();
            
            if (!$col->getNull()) {
                $sql .= " NOT NULL";
            }
            
            if (!is_null($col->getDefault())) {
                $sql .= " DEFAULT '".$col->getDefault()."'";
            }
            
            if (!is_null($col->getExtra())) {
                $sql .= " ".$col->getExtra();
            }
            
            $sql .= ",\n";
        }
        
        $sql .= ") DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci\n";
        
        print_r($this->query($sql));
        exit;
    }
    
    /**
     * Alter a database table
     * 
     * @param PHPFrame_DatabaseTable $table A reference to an object of type 
     *                                      PHPFrame_DatabaseTable representing 
     *                                      the table we want to alter.
     * 
     * @return void
     * @since  1.0
     * @todo   This method needs to be implemented.
     */
    public function alterTable(PHPFrame_DatabaseTable $table)
    {
        
    }
    
    /**
     * Truncate a database table. This method deletes all records from a table 
     * and resets the auto increment counter back to zero. 
     * 
     * @param string $table_name The name of the table we want to truncate.
     * 
     * @return void
     * @since  1.0
     */
    public function truncate($table_name)
    {
        $sql = "TRUNCATE TABLE `".$table_name."`";
        
        // Run SQL query
        $this->query($sql);
    }
    
    /**
     * Get the columns of a given table
     * 
     * @param string $table_name The name of the table for which we want to get 
     *                           the columns.
     * 
     * @return array
     * @since  1.0
     * @todo   This method needs to be implemented.
     */
    public function getColumns($table_name)
    {
        // Store structure in array uning table name as key
        //$sql = "SHOW COLUMNS FROM `".$table_name."`";
        //$structure[$table_name] = $this->fetchAssocList($sql);
    }
}
