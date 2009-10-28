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
        if (is_null($table_name)) {
            $table_name = strtolower(get_class($obj));
        }
        
        $table = new PHPFrame_DatabaseTable($db, $table_name);
        print_r($table); exit;
        
        $fields     = array_keys(iterator_to_array($obj));
        $filters    = $obj->getFilters();
        
        if ($drop) {
            $sql = "DROP TABLE IF EXISTS `".$table_name."`";
            $db->query($sql);
        }
        
        $sql = "CREATE TABLE `".$table_name."` (";
        
        foreach ($fields as $field) {
            $sql .= "\n`".$field."` ";
            
            if (array_key_exists($field, $filters)) {
                $filter  = $filters[$field];
                $options = $filter->getOptions();
                
                if ($filter instanceof PHPFrame_BoolFilter) {
                    $sql .= "enum('0','1')";
                } elseif ($filter instanceof PHPFrame_IntFilter) {
                    $range = $options["max_range"] - $options["min_range"];
                    if ($range <= 255) { // 1 byte int
                        $sql .= "tinyint(4)";
                    } elseif($range <= 65535) { // 2 byte int
                        $sql .= "smallint(6)";
                    } elseif($range <= 16777215) { // 3 byte int
                        $sql .= "mediumint(9)";
                    } elseif($range <= 4294967295) { // 4 byte int
                        $sql .= "int(11)";
                    } else { // 8 byte int
                        $sql .= "bigint(21)";
                    }
                } elseif ($filter instanceof PHPFrame_FloatFilter) {
                    $sql .= "float";
                } elseif ($filter instanceof PHPFrame_EnumFilter) {
                    $sql .= "enum('".implode("','". $option["enums"])."')";
                } elseif ($filter instanceof PHPFrame_StringFilter) {
                    if ($options["max_length"] > 0) {
                        $sql .= "varchar(".$options["max_length"].")";
                    } else {
                        $sql .= "text";
                    }
                }
            }
            
            if (!$obj->allowsNull($field)) {
                $sql .= " NOT NULL";
            }
            
            $def_values = iterator_to_array($obj);
            if (!is_null($def_values[$field])) {
                $sql .= " DEFAULT '".$def_values[$field]."'";
            }
            
            if ($field == "id") {
                $sql .= " AUTO_INCREMENT";
            }
            
            $sql .= ",";
        }
        $sql .= "\nPRIMARY KEY (`id`)";
        $sql .= "\n)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci\n";
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
