<?php
class PHPFrame_MySQLDatabase extends PHPFrame_Database
{
	protected function connect()
	{
		parent::connect();
		
        $this->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
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
