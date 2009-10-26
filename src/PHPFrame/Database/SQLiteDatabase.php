<?php
class PHPFrame_SQLiteDatabase extends PHPFrame_Database
{
	/**
     * Executes an SQL statement, returning a result set as a PDOStatement object 
     * 
     * @param string $sql        The SQL statement to run 
     * @param array  $params     An array with the query parameters if any
     * @param int    $fetch_mode Mode in which to fetch the query result
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function query($sql, $params=array(), $fetch_mode=self::FETCH_STMT)
    {
    	$pattern = '/(\s|\.|\(|,)`?(id)`?(\s|\.|\)|,)/';
    	$sql     = preg_replace($pattern, '$1row$2$3', $sql);
    	
    	parent::query($sql, $params, $fetch_mode);
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
        // Get list of all tables in database
        $sql = "SELECT tbl_name FROM sqlite_master WHERE type = 'table'";
        
        $tables = array();
        foreach ($this->fetchColumnList($sql) as $table) {
            $tables[] = new PHPFrame_DatabaseTable($this, $table);
        }
        
        return $tables;
    }
    
    /**
     * Get the columns of a given table
     * 
     * @param string $table_name
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getColumns($table_name)
    {
        // Get list of all tables in database
        $sql     = "SELECT sql FROM sqlite_master ";
        $sql    .= "WHERE type = 'table' AND name = :table_name";
        $params  = array(":table_name" => $table_name);
        
        $sql     = $this->fetchColumn($sql, $params);
        
        $pattern = '/CREATE\s+TABLE\s+`?'.$table_name.'`?\s+\(\s((.|\s)*)\s\)/i';
        preg_match($pattern, $sql, $matches);
        
        if (!isset($matches[1])) {
        	$msg = "Could not read column information from database";
        	throw new RuntimeException($msg);
        }
        
        $lines = explode(",", $matches[1]);
        foreach ($lines as $line) {
        	$line = trim($line);
        	preg_match_all('/([\w]+)/i', $line, $matches);
        	
        	for ($i=0; $i<count($matches); $i++) {
        		$tokens         = $matches[$i];
        		$col            = array();
        		$col["name"]    = $tokens[0];
        		$col["type"]    = $tokens[1];
        		$col["default"] = null;
        		$col["null"]    = true;
        		
        		for ($j=2; $j<count($tokens); $j++) {
        		    if ($tokens[$j] == "DEFAULT") {
        		        $col["default"] = $tokens[++$j];
        		    } elseif ($tokens[$j] == "NOT") {
	        		    if ($tokens[++$j] == "NULL") {
	                        $col["null"] = false;
	                    }
        		    }
        		}
        		
        	    $array[] = $col;
        	    
        	    // skip odd rows
        	    $i++;
        	}
        }
        
        //print_r($array); exit;
        
        $columns = array();
        
        foreach ($array as $col) {
            $obj = new PHPFrame_DatabaseColumn();
            $obj->setName($col["name"]);
            $obj->setType($col["type"]);
            $obj->setDefault($col["default"]);
            $obj->setNull($col["null"]);
            
            $columns[] = $obj;
        }
        
        return $columns;
    }
}
