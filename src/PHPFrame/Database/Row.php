<?php
/**
 * PHPFrame/Database/Row.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Row Class
 * 
 * The "row" class is an abstraction of a table row in a database.
 * 
 * Note that this class uses the Application Registry object to cache table 
 * structures and primary keys in order to avoid unnecessary trips to the database.
 * 
 * @todo This class needs to be refactored so that "fields" are all objects that
 *       inherit from a common super class. At the moment we deal with both "field" 
 *       objects and string values. This is causing the appearance of parallel 
 *       conditional statements. Mainly: if ($field instanceof PHPFrame_Database_Field).
 *       
 *       Special attention should also be paid to the handling of "foreign" fields
 *       as the result of queries with JOIN clauses.
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @deprecated
 * @see PHPFrame_Mapper
 */
class PHPFrame_Database_Row
{
    /**
     * A reference to the DB connection to use for mapping the row
     * 
     * @var PHPFrame_Database
     */
    private $_db=null;
    /**
     * An IdObject used to map the row to the db
     * 
     * @var PHPFrame_Database_IdObject
     */
    private $_id_obj=null;
    /**
     * An array containing the "field" objects that make up the row
     * 
     * @var array
     */
    private $_fields=array();
    
    /**
     * Constructor
     * 
     * The constructor takes only one required parameter ($table_name), that being
     * the name of the database table the row will mapped to.
     * 
     * Note that this can be overriden after instantiation when invoking 
     * PHPFrame_Database_Row::load(), as it can take an IdObject instead of an 
     * id as an argument.
     * 
     * @param string            $table_name  The table to map this row to in the db.
     * @param PHPFrame_Database $db          Optionally use an alternative database 
     *                                       to the default one provided by 
     *                                       PHPFrame::DB() as defined in config 
     *                                       class.
     * @param string            $primary_key This parameter allows to set a different
     *                                       primary key from the one automatically
     *                                       read from the table structure in the db.
     * @access public
     * @return void
     * @see    PHPFrame_Database_IdObject, PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function __construct(
        $table_name,
        PHPFrame_Database $db=null,
        $primary_key=null
    ) {
        $table_name = (string) $table_name;
        
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
        
        // Acquire IdObject
        $this->_id_obj = new PHPFrame_Database_IdObject();
        // Initialise fiels selection and table name in IdObject
        $this->_id_obj->select("*")->from($table_name);
        
        // Read table structure from application registry
        $this->_fetchFields();
        
        // Override primary key detected from db with given value
        if (!is_null($primary_key)) {
            $this->setPrimaryKey($primary_key);
        }
    }
    
    /**
     * Magic method invoked when trying to use an IdObject as a string.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Magic getter
     * 
     * This is called when we try to access a public property and tries to 
     * find the key in the columns array.
     * 
     * This method also enforces that public properties are not mistakenly 
     * referenced.
     * 
     * @param string $key The key to retrieve a value from internal array.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    /**
     * Convert object to string
     * 
     * @param bool $show_keys Boolean to indicate whether we want to show the
     *                        column names. Default is TRUE.
     *                        
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString($show_keys=true)
    {
        $str = "";
        
        foreach ($this->_fields as $field) {
            if ($show_keys) {
                $str .= $field->getField().": ".$field->getValue()."\n";
            } else {
                $str .= PHPFrame_Base_String::fixLength($field->getValue(), 16)."\t";
            }
        }
        
        return $str;
    }
    
    /**
     * Return Row object as associative array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function toArray()
    {
        $array = array();
        
        foreach ($this->_fields as $key=>$value) {
            if ($value instanceof PHPFrame_Database_Field) {
                $value = $value->getValue();
            }
            
            $array[$key] = $value;
        }
        
        return $array;
    }
    
    /**
     * Get a column value from this row
     * 
     * @param string $field_name The column we want to get the value for.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function get($field_name)
    {
        $field_names = array_keys($this->_fields);
        
        if (in_array($field_name, $field_names)) {
            if ($this->_fields[$field_name] instanceof PHPFrame_Database_Field) {
                return $this->_fields[$field_name]->getValue();
            }
            
            return $this->_fields[$field_name];
        }
        
        throw new PHPFrame_Exception("Tried to get column '".$field_name
                                     ."' that doesn't exist in "
                                     .$this->_id_obj->getTableName(), 
                                      PHPFrame_Exception::WARNING);
    }
    
    public function getPrimaryKey()
    {
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field
                && $field->isPrimaryKey()) {
                return $field->getField();
            }
        }
        
        return null;
    }
    
    public function getPrimaryKeyValue()
    {
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field
                && $field->isPrimaryKey()) {
                return $field->getValue();
            }
        }
        
        return null;
    }
    
    /**
     * Get column keys
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getFields()
    {
        $array = array();
        
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field) {
                $array[] = $field->getField();
            } else {
                $array[] = $field;
            }
            
        }
        
        return $array;
    }
    
    /**
     * Set a column value in this row
     *  
     * @param string $property The column we want to set the value for.
     * @param string $value    The value to set the column to.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($property, $value)
    {
        if (!$this->hasField($property)) {
            throw new PHPFrame_Exception("Tried to set column '".$property
                                         ."' that doesn't exist in "
                                         .$this->_id_obj->getTableName(), 
                                          PHPFrame_Exception::WARNING);
        }
        
        foreach ($this->_fields as $key=>$field) {
            if ($key == $property) {
                if ($field instanceof PHPFrame_Database_Field) {
                    $field->setValue($value);
                } else {
                    $this->_fields[$key] = $value;
                }
            }
        }
    }
    
    public function setPrimaryKey($field_name)
    {
        foreach ($this->_fields as $field) {
            // Reset other fields that might have been set as primary keys
            if ($field instanceof PHPFrame_Database_Field) {
                if ($field->isPrimaryKey()) {
                    $field->setPrimaryKey("");
                }
                
                // Set the field as primary key
                if ($field->getField() == $field_name) {
                    $field->setPrimaryKey("PRI");
                }
            }
        }
    }
    
    public function setPrimaryKeyValue($value)
    {
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field 
                && $field->isPrimaryKey()) {
                $field->setValue($value);
            }
        }
    }
    
    /**
     * Check if row has a given column
     * 
     * @param string $column_name The column name we want to check.
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function hasField($column_name)
    {
        // Loop through table structure to find key
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field
                && $field->getField() == $column_name
            ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load row data from database given a row id.
     * 
     * @param int|string|PHPFrame_Database_IdObject $id      Normally an integer or string 
     *                                                       with the primary key value of
     *                                                       the row we want to load.
     *                                                       Alternatively you can pass an
     *                                                       IdObject.
     * @param string                                $exclude A list of key names to exclude 
     *                                                       from binding process separated 
     *                                                       by commas.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function load($id, $exclude='')
    {
        if ($id instanceof PHPFrame_Database_IdObject) {
            $this->_id_obj = $id;
            // Re-read table structure taking into account join tables
            $this->_fetchFields();
        } else {
            $this->_id_obj->where($this->getPrimaryKey(), "=", ":id");
            $this->_id_obj->params(":id", $id);
        }
        
        // Cast IdObject to string to convert to SQL query
        $sql = (string) $this->_id_obj;
        
        // Fetch data as assoc array
        $array = $this->_db->fetchAssoc($sql, $this->_id_obj->getParams());
        
        // If result is array we bind it to the row
        if (is_array($array) && count($array) > 0) {
            $this->bind($array, $exclude);   
        }
        
        return $this;
    }
    
    /**
     * Bind array to row
     * 
     * @param array  $array   The array to bind to the object.
     * @param string $exclude A list of key names to exclude from binding 
     *                        process separated by commas.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function bind($array, $exclude='')
    {
        // Process exclude
        if (!empty($exclude)) {
            $exclude = explode(',', $exclude);
        } else {
            $exclude = array();
        }
        
        if (!is_array($array)) {
            $msg = 'Argument 1 ($array) has to be of type array.';
            throw new PHPFrame_Exception_Database($msg);
        }
        
        if (count($array) > 0) {
            // Rip values using known structure
            foreach ($array as $key=>$value) {
                if (!in_array($key, $exclude)) {
                    if (isset($this->_fields[$key]) 
                        && $this->_fields[$key] instanceof PHPFrame_Database_Field
                    ) {
                        $this->_fields[$key]->setValue($value);
                    } else {
                        $this->_fields[$key] = $value;
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Store row in database
     * 
     * @todo Have to find a way to make row objects handle the decision of 
     *       whether they should update or insert depending. Maybe a way would
     *       be to track whether the object's data originated from a db query 
     *       (load method) and/or checking whether an entry already exists with
     *       the selected primary key value.
     * 
     * @param bool $force_insert A flag to indicate whether we want to force an
     *                           INSERT query instead of UPDATE. This has been 
     *                           added as temporary solution to storing rows that 
     *                           do not have a numeric "auto_incremet" primary key.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function store($force_insert=false)
    {
        // Check types and required columns before saving
        $this->_check();
        
        // Do insert or update depending on whether primary key is set
        $id = $this->getPrimaryKeyValue();
        
        if ($force_insert || is_null($id) || empty($id)) {
            // Insert new record
            $this->_insert();
        } else {
            $this->_update();
        }
        
        return $this;
    }
    
    /**
     * Delete row from database
     * 
     * @param int|string $id Normally an integer value reprensting the row primary 
     *                       key value. This could also be a string.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $sql = "DELETE FROM `".$this->_id_obj->getTableName()."` ";
        $sql .= " WHERE `".$this->getPrimaryKey()."` = :id";
        
        $this->_db->query($sql, array(":id"=>$id));
    }
    
    /**
     * Read row structure from database and store in app registry
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchFields()
    {
        // Reset the fields array before we fetch fields
        $this->_fields = array();
        
        $table_name = $this->_id_obj->getTableName();
        $db_driver = PHPFrame::Config()->get('db.driver');
        //MySQL on Windows converts all table names to lower case by default.
        //this hack should handle this case
        if (DS=='\\' && $db.driver=='MySQL')
            $table_name = strtolower($table_name);
        // Fetch the structure of the table that contains this row
        $table_structure = $this->_db->getStructure($table_name);
        
        // Loop through structure array to build field objects
        $field_names = $this->_id_obj->getSelectFields();
        foreach ($table_structure as $field_array) {
            $this->_fields[$field_array["Field"]] = new PHPFrame_Database_Field($field_array);
        }
    }
    
    /**
     * Check columns data types and required fields before saving to db.
     * 
     * This method throws an exception if validation fails
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _check()
    {
        // Delegate field validation to field objects
        foreach ($this->_fields as $field) {
            if ($field instanceof PHPFrame_Database_Field) {
                $field->isValid();
            }
        }
    }
    
    /**
     * Insert new record into database
     *   
     * @access private
     * @return void
     * @since  1.0
     */
    private function _insert()
    {
        // Build SQL insert query
        $sql = "INSERT INTO `".$this->_id_obj->getTableName()."` ";
        $params = array();
        
        foreach ($this->_fields as $field) {
            // Only take into account fields of type PHPFrame_Database_Field
            // because these are the "real" columns in the db table
            if ($field instanceof PHPFrame_Database_Field) {
                $columns[] = $field->getField();
                
                if ($field->getValue() == "NULL") {
                    $values[] = $field->getValue();
                } else {
                    $values[] = ":".$field->getField();
                    $params[":".$field->getField()] = $field->getValue();
                }
            }
        }
        
        $sql .= " (`".implode("`, `", $columns)."`) ";
        $sql .= " VALUES (".implode(", ", $values).")";
        
        $insert_id = $this->_db->query($sql, $params, PHPFrame_Database::FETCH_LAST_INSERTID);
        
        $this->setPrimaryKeyValue($insert_id);
    }
    
    /**
     * Update existing row in database
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _update()
    {
        // Build SQL insert query
        $sql = "UPDATE `".$this->_id_obj->getTableName()."` SET ";
        $params = array();
        
        $i=0;
        foreach ($this->_fields as $field) {
            // Only take into account fields of type PHPFrame_Database_Field
            // because these are the "real" columns in the db table
            // If field is null we ignore the field in the UPDATE statement
            if ($field instanceof PHPFrame_Database_Field 
                && !$field->isPrimaryKey()
                && !is_null($field->getValue())) {
                if ($i>0) {
                    $sql .= ", ";
                }
                
                if ($field->getValue() == "NULL") {
                    $values[] = $field->getValue();
                    $sql .= " `".$field->getField()."` = ".$field->getValue();
                } else {
                    $sql .= " `".$field->getField()."` = :".$field->getField();
                    $params[":".$field->getField()] = $field->getValue();
                }
                
                $i++;
            }
        }
        
        $sql .= " WHERE `".$this->getPrimaryKey()."` = '";
        $sql .= $this->getPrimaryKeyValue()."'";
        
        // Run the update query
        $this->_db->query($sql, $params);
    }
}
