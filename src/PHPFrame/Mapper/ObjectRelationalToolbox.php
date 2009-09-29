<?php
class PHPFrame_ObjectRelationalToolbox
{
    /**
     * Create a database table to store a given persistent object
     * 
     * @param PHPFrame_Database         $db
     * @param PHPFrame_PersistentObject $obj
     * @param string                    $table_name [Optional] Table name to 
     *                                              use. If none specified we 
     *                                              use the object's class name.
     * @param bool                      $drop       [Optional] Default value is 
     *                                              FALSE. When set to true 
     *                                              existing table will be 
     *                                              dropped.
     * 
     * @access public
     * @return void
     * @throws Exception on failure
     * @since  1.0
     */
    public function createTable(
        PHPFrame_Database $db, 
        PHPFrame_PersistentObject $obj,
        $table_name=null,
        $drop=false
    )
    {
        $table_name = (is_null($table_name)) ? strtolower(get_class($obj)) : $table_name;
        $fields     = array_keys(iterator_to_array($obj));
        $filters    = $obj->getFilters();
        
        if ($drop) {
            $sql = "DROP TABLE IF EXISTS `".$table_name."`";
            $db->query($sql);
        }
        
        $sql = "CREATE TABLE `".$table_name."` (";
        
        foreach ($fields as $field) { 
            $obj->isValid();
            
            $filter = $obj->getFilter($field);
            
            $sql .= "\n`".$field."` ";
            $sql .= $filter["type"];
            
            if (empty($filter["max_length"])) {
                switch ($filter["type"]) {
                    case "int" :
                        $sql .= "(11)";
                        break;
                    case "varchar" :
                        $sql .= "(50)";
                        break;
                    case "enum" :
                    case "text" :
                        break;
                }
            } elseif ($filter["type"] == "enum") {
                $sql .= "('".implode("','", $filter["max_length"])."')";
            } elseif ($filter["type"] == "varchar") {
                $sql .= "(".$filter["max_length"].")";
            }
            
            if (!isset($filter["allow_null"]) || !$filter["allow_null"]) {
                $sql .= " NOT NULL";
            } elseif ($filter["allow_null"]) {
                $sql .= " DEFAULT NULL";
            }
            
            if (isset($filter["def_value"]) && !is_null($filter["def_value"]) && !$filter["allow_null"]) {
                $sql .= " DEFAULT '".$filter["def_value"]."'";
            }
            
            if ($field == "id") {
                $sql .= " AUTO_INCREMENT";
            }
            
            $sql .= ",";
        }
        $sql .= "\nPRIMARY KEY (`id`)";
        $sql .= "\n)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci\n";
        //echo $sql; exit;
        $db->query($sql);
    }
    
    /**
     * Create the php code to represent a given database table as a persistent
     * object.
     * 
     * @param PHPFrame_Database $db
     * @param string            $table_name
     * 
     * @access public
     * @return string
     * @throws Exception on failure
     * @since  1.0
     */
    public function createPersistentObjectClass(
        PHPFrame_Database $db, 
        $table_name
    )
    {
        
    }
    
    /**
     * Check whether a database table is valid to store a given persistent
     * object.
     * 
     * @param PHPFrame_Database         $db
     * @param string                    $table_name
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isValid(
        PHPFrame_Database $db, 
        $table_name,
        PHPFrame_PersistentObject $obj
    )
    {
        
    }
}
