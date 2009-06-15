<?php
/**
 * PHPFrame/Database/Row.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Row Class
 * 
 * Note that this class uses the Application Registry object to cache table 
 * structures and primary keys in order to avoid unnecessary trips to the database.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
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
     * Primary key
     * 
     * @var array
     */
    private $_primary_key=null;
    /**
     * Table structure
     * 
     * @var array
     */
    private $_structure=null;
    /**
     * The table name where the row object belongs
     * 
     * @var string
     */
    private $_table_name=null;
    /**
     * The row's data
     * 
     * @var    array
     */
    private $_data=array();
    
    /**
     * Constructor
     * 
     * @param string            $table_name The table to map this row to in the db.
     * @param PHPFrame_Database $db         Optionally use an alternative database 
     *                                      to the default one provided by 
     *                                      PHPFrame::DB() as defined in config 
     *                                      class.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($table_name, PHPFrame_Database $db=null) 
    {
        $this->_table_name = (string) $table_name;
        
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
        
        // Read table structure from application registry
        $this->_readStructure();
    }
    
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
        
        foreach ($this->_data as $key=>$value) {
            if ($show_keys) {
                $str .= $key.": ".$value."\n";
            } else {
                $str .= PHPFrame_Base_String::fixLength($value, 16)."\t";
            }
        }
        
        return $str;
    }
    
    /**
     * Get a column value from this row
     * 
     * @param string $key The column we want to get the value for.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function get($key) 
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } elseif (!$this->hasColumn($key)) {
            throw new PHPFrame_Exception("Tried to get column '".$key
                                         ."' that doesn't exist in "
                                         .$this->_table_name, 
                                         PHPFrame_Exception::E_PHPFRAME_WARNING);
        } else {
            return null;
        }
    }
    
    /**
     * Set a column value in this row
     *  
     * @param string $key   The column we want to set the value for.
     * @param string $value The value to set the column to.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        if (!$this->hasColumn($key)) {
            throw new PHPFrame_Exception("Tried to set column '".$key
                                         ."' that doesn't exist in "
                                         .$this->_table_name, 
                                         PHPFrame_Exception::E_PHPFRAME_WARNING);
        }
        
        $this->_data[$key] = $value;
    }
    
    /**
     * Get column keys
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getKeys()
    {
        $array = array();
        
        foreach ($this->_structure as $col) {
            $array[] = $col->Field;
        }
        
        return $array;
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
    public function hasColumn($column_name) 
    {
        // Loop through table structure to find key
        foreach ($this->_structure as $structure) {
            if ($structure->Field == $column_name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load row data from database given a row id.
     * 
     * @param mixed  $id      Normally an integer, but could be a string
     * @param string $exclude A list of key names to exclude from binding process 
     *                        separated by commas.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function load($id, $exclude='') 
    {
        $query = "SELECT * FROM `".$this->_table_name;
        $query .= "` WHERE `".$this->_primary_key."` = '".$id."'";
        
        $array = $this->_db->loadAssoc($query);
        
        if (is_array($array) && count($array) > 0) {
            $this->bind($array, $exclude);
            
            return $this;    
        } else {
            return false;
        }
    }
    
    /**
     * Load row data from database using query
     * 
     * @param string $query        The SQL query used to load the data.
     * @param array  $foreign_keys An array with foreign keys to be allowed to be 
     *                             set as columns in this row.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function loadByQuery($query, $foreign_keys=array()) 
    {
        // Run SQL query
        $stmt = $this->_db->query($query);
        
        $array = array();
        
        // Id result is valid we populate array
        if ($stmt->rowCount() == 1) {
            $array = $stmt->fetch(PDO::FETCH_ASSOC);
            // Bind result array to row passing foreign keys to allow them
            $this->bind($array, '', $foreign_keys);
        }
        
        return $this;
    }
    
    /**
     * Bind array to row
     * 
     * @param array  $array        The array to bind to the object.
     * @param string $exclude      A list of key names to exclude from binding 
     *                             process separated by commas.
     * @param array  $foreign_keys An array with foreign keys to be allowed to be 
     *                             set as columns in this row.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function bind($array, $exclude='', $foreign_keys=array()) 
    {
        // Process exclude
        if (!empty($exclude)) {
            $exclude = explode(',', $exclude);
        } else {
            $exclude = array();
        }
        
        if (!is_array($array)) {
            $exception_msg = 'Argument 1 ($array) has to be of type array.';
            throw new PHPFrame_Exception_Database($exception_msg);
        }
        
        if (count($array) > 0) {
            // Rip values using known structure
            foreach ($this->_structure as $col) {
                if (array_key_exists($col->Field, $array) 
                    && !in_array($col->Field, $exclude)
                ) {
                    $this->_data[$col->Field] = $array[$col->Field];
                }
            }
            
            // Add foreign key values
            foreach ($foreign_keys as $foreign_key) {
                if (array_key_exists($foreign_key, $array) 
                    && !in_array($foreign_key, $exclude)
                ) {
                    $this->_data[$foreign_key] = $array[$foreign_key];
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Store row in database
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function store() 
    {
        // Check types and required columns before saving
        $this->_check();
        
        // Do insert or update depending on whether primary key is set
        $id = $this->get($this->_primary_key);
        if (is_null($id)) {
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
        $query = "DELETE FROM `".$this->_table_name."` ";
        $query .= " WHERE `".$this->_primary_key."` = '".$id."'";
        $this->_db->query($query);
    }
    
    /**
     * Read row structure from database and store in app registry
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _readStructure() 
    {
        $app_registry = PHPFrame::AppRegistry();
        $table_structures = $app_registry->get('table_structures');
        $table_primary_keys = $app_registry->get('table_primary_keys');
        
        // Load structure from db if not in application registry already
        if (!is_array($table_structures[$this->_table_name])) {
            $query = "SHOW COLUMNS FROM `".$this->_table_name."`";
            $this->_structure = $this->_db->loadObjectList($query);
            
            if ($this->_structure === false || !is_array($this->_structure)) {
                $exception_msg = "Couldn't read table structure for ";
                $exception_msg .= $this->_table_name;
                throw new PHPFrame_Exception_Database($exception_msg);
            }
            
            // Loop through structure array to find primary key
            foreach ($this->_structure as $col) {
                if ($col->Key == 'PRI') {
                    $this->_primary_key = $col->Field;
                }
            }
            
            $table_structures[$this->_table_name] = $this->_structure;
            $table_primary_keys[$this->_table_name] = $this->_primary_key;
            // Store data in app registry
            $app_registry->set('table_structures', $table_structures);
            $app_registry->set('table_primary_keys', $table_primary_keys);
        } else {
            $this->_structure = $table_structures[$this->_table_name];
            $this->_primary_key = $table_primary_keys[$this->_table_name];
        }
    }
    
    /**
     * Check columns data types and required fields before saving to db.
     * 
     * @access private
     * @return bool
     * @since  1.0
     */
    private function _check() 
    {
        // Loop through every column in the row
        foreach ($this->_structure as $structure) {
            
            // If assigned value is empty
            if (empty($this->_data[$structure->Field])) {
                // Set default value if any
                if (!is_null($structure->Default)) {
                    $this->_data[$structure->Field] = $structure->Default;
                }
                
                // If column is timestamp and default value is CURRENT_TIMESTAMP 
                // replace with current date
                if ($structure->Default == "CURRENT_TIMESTAMP") {
                    $this->_data[$structure->Field] = date("Y-m-d H:i:s");
                }
                
                // If column is auto_increment we set to null
                if ($structure->Extra == 'auto_increment') {
                    $this->_data[$structure->Field] = null;
                    continue; // jump to next iteration of the loop to avoid check
                }
            }
            
            // Get type and length from input type string
            preg_match('/([a-zA-Z]+)(\((.+)\))?/i', $structure->Type, $matches);
            
            $type = strtolower($matches[1]); // make string lower case
            if (isset($matches[3])) {
                $length = $matches[3];    
            }
            
            // Make type variation prefix (ie: tinyint to int or longtext to long)
            $prefixes = array('tiny', 'small', 'medium', 'big', 'long');
            $type = str_replace($prefixes, '', $type);
            
            // Perform validation depending on data type
            switch ($type) {
            case 'int' : 
                $pattern = '/^-?\d+$/';
                break;
                    
            case 'float' :
            case 'double' :
            case 'decimal' :
                $pattern = '/^-?\d+\.?\d+$/';
                break;
                    
            case 'char' :
            case 'varchar' :
                $pattern = '/^.{0,'.$length.'}$/';
                break;
                
            case 'text' :
            case 'blob' :
            case 'enum' :
            case 'datetime' :
            case 'date' :
            case 'time' :
            case 'year' :
            case 'timestamp' :
            case 'binary' :
            case 'bool' :
            default : 
                $pattern = '/^.*$/im';
                break;
            }
            
            if (!preg_match($pattern, $this->_data[$structure->Field])) {
                $exception_msg = "Wrong type for column '".$structure->Field."'. ";
                $exception_msg .= "Expected '".$structure->Type."' and got '";
                $exception_msg .= $this->_data[$structure->Field]."'";
                $exception_code = PHPFrame_Exception::E_PHPFRAME_WARNING;
                throw new PHPFrame_Exception($exception_msg, $exception_code);
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
        $query = "INSERT INTO `".$this->_table_name."` ";
        $query .= " (`".implode("`, `", array_keys($this->_data))."`) ";
        $query .= " VALUES ('".implode("', '", $this->_data)."')";
        //echo $query; exit;
        
        $insert_id = $this->_db->query($query);
        
        if ($insert_id === false) {
            throw new PHPFrame_Exception($this->_db->getLastError());
        }
        
        $this->_data[$this->_primary_key] = $insert_id;
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
        $query = "UPDATE `".$this->_table_name."` SET ";
        $i=0;
        foreach ($this->_data as $key=>$value) {
            if ($i>0) {
                $query .= ", ";
            }
            $query .= " `".$key."` = '".$value."' ";
            $i++;
        }
        $query .= " WHERE `".$this->_primary_key."` = '";
        $query .= $this->_data[$this->_primary_key]."'";
        
        if ($this->_db->query($query) === false) {
            throw new PHPFrame_Exception("Error updating database row",
                                         PHPFrame_Exception::E_PHPFRAME_WARNING,
                                         "Query: ".$query);
        }
        
    }
}
