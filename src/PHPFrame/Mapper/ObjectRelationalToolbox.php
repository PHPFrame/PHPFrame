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
            $table_name = get_class($obj);
        }
        
        $table = new PHPFrame_DatabaseTable($db, $table_name);
        
        
        foreach ($obj->getFilters() as $key=>$filter) {
        	$column = new PHPFrame_DatabaseColumn(array(
        	    "name"=>$key, 
        	    "type"=>PHPFrame_DatabaseColumn::TYPE_BLOB
        	));
        	
        	$options = $filter->getOptions();
        	
            if ($filter instanceof PHPFrame_BoolFilter) {
                $column->setType(PHPFrame_DatabaseColumn::TYPE_BOOL);
            } elseif ($filter instanceof PHPFrame_IntFilter) {
                $range = $options["max_range"] - $options["min_range"];
                if ($range <= 255) { // 1 byte int
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_TINYINT);
                } elseif($range <= 65535) { // 2 byte int
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_SMALLINT);
                } elseif($range <= 16777215) { // 3 byte int
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_MEDIUMINT);
                } elseif($range <= 4294967295) { // 4 byte int
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_INT);
                } else { // 8 byte int
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_BIGINT);
                }
            } elseif ($filter instanceof PHPFrame_FloatFilter) {
                $column->setType(PHPFrame_DatabaseColumn::TYPE_FLOAT);
            } elseif ($filter instanceof PHPFrame_EnumFilter) {
            	$column->setType(PHPFrame_DatabaseColumn::TYPE_ENUM);
            } elseif ($filter instanceof PHPFrame_StringFilter) {
                if ($options["max_length"] > 0) {
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_VARCHAR);
                } else {
                    $column->setType(PHPFrame_DatabaseColumn::TYPE_TEXT);
                }
            }
            
            $column->setNull($obj->allowsNull($key));
            
            $def_values = iterator_to_array($obj);
            if (!is_null($def_values[$key])) {
                $column->setDefault($def_values[$key]);
            }
            
            if ($key == "id") {
            	$column->setKey(PHPFrame_DatabaseColumn::KEY_PRIMARY);
            	$column->setExtra(PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT);
            }
            
        	$table->addColumn($column);
        }
        
        $db->createTable($table);
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
