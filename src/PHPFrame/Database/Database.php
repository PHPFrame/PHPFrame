<?php
/**
 * PHPFrame/Database/Database.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Database.php 32 2009-06-09 04:47:23Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Database class
 * 
 * This class deals with the connection(s) to the database(s).
 * 
 * The database class serves singleton objects for each connection (determined by 
 * the dsn and db user credentials.
 * 
 * This class also extends PHPFrame_Base_Subject allowing observer objects to 
 * subscribe for updates.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Base_Subject
 * @since      1.0
 */
class PHPFrame_Database extends PHPFrame_Base_Subject
{
    /**
     * Return query() result as single primitive value
     * 
     * @var int
     */
    const FETCH_COLUMN=0;
    /**
     * Return query() result as numerically indexed array
     * 
     * @var int
     */
    const FETCH_ARRAY=1;
    /**
     * Return query() result as a list of numerically indexed arrays
     * 
     * @var int
     */
    const FETCH_ARRAY_LIST=2;
    /**
     * Return query() result as associative array
     * 
     * @var int
     */
    const FETCH_ASSOC=3;
    /**
     * Return query() result as a list of associative arrays
     * 
     * @var int
     */
    const FETCH_ASSOC_LIST=4;
    /**
     * Return query() result as object
     * 
     * @var int
     */
    const FETCH_OBJ=5;
    /**
     * Return query() result as a list of objects
     * 
     * @var int
     */
    const FETCH_OBJ_LIST=6;
    /**
     * Return query() result as number of rows returned
     * 
     * @var int
     */
    const FETCH_NUM_ROWS=7;
    /**
     * Return query() result as number of records affected
     * 
     * @var int
     */
    const FETCH_AFFECTED_ROWS=8;
    /**
     * Return query() result as last insert id
     * 
     * @var int
     */
    const FETCH_LAST_INSERTID=9;
    /**
     * Return PDOStatement object from query() method
     * 
     * @var int
     */
    const FETCH_STMT=10;
    
    /**
     * An array holding instances of this class
     * 
     * @var array containing objects of type PHPFrame_Database
     */
    private static $_instances=array();
    /**
     * A string used as key in the instances array for current object
     * 
     * @var string
     */
    private $_key=null;
    /**
     * DSN object used for the database connection
     * 
     * @var PHPFrame_Database_DSN
     */
    private $_dsn=null;
    /**
     * Database user if any
     * 
     * @var string
     */
    private $_db_user=null;
    /**
     * Database password if any
     * 
     * @var string
     */
    private $_db_pass=null;
    /**
     * A PDO object that represents the connection to the database server.
     * 
     * @var PDO
     */
    private $_pdo=null;
    /**
     * A reference to the PDOStatement object from the last query
     * 
     * @var PDOStatement
     */
    private $_stmt=null;
    /**
     * An array containing data about the tables in this database and their fields
     * 
     * @var array
     */
    private $_structure=array();
    
    /**
     * Constructor
     * 
     * The constructor connects to the MySQL server and selects the database.
     * 
     * @param PHPFrame_Database_DSN $dsn     A Database Source Name object.
     * @param string                $db_user The database user name if any.
     * @param string                $db_pass The database password if any.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct(
        PHPFrame_Database_DSN $dsn, 
        $db_user=null, 
        $db_pass=null
    ) {
        // Set internal properties
        $this->_dsn = $dsn;
        $this->_db_user = $db_user;
        $this->_db_pass = $db_pass;
        
        // Connect to database server
        $this->_connect();
    }
    
    /**
     * Magic method invoked when an instance is serialized.
     * 
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        // Disconnect from database server
        $this->_pdo = null;
        
        return array_keys(get_object_vars($this));
    }
    
    /**
     * Magic method invoked when a new instance is unserialized
     * 
     * @return void
     * @since  1.0
     */
    public function __wakeup()
    {   
        // Re-connect to database server
        $this->_connect();
    }
    
    /**
     * Get Instance
     * 
     * @param PHPFrame_Database_DSN $dsn     A Database Source Name object.
     * @param string                $db_user The database user name if any.
     * @param string                $db_pass The database password if any.
     * 
     * @return PHPFrame_Database
     */
    public static function getInstance(
        PHPFrame_Database_DSN $dsn, 
        $db_user=null, 
        $db_pass=null
    ) {
        $key = $dsn->toString();
        if (!is_null($db_user)) {
            $key .= ";user=".$db_user;
        }
        
        if (!isset(self::$_instances[$key])) {
            self::$_instances[$key] = new self($dsn, $db_user, $db_pass);
            self::$_instances[$key]->_key = $key;
        }
        
        return self::$_instances[$key];
    }
    
    /**
     * Return the database structure
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getStructure()
    {
        if (is_null($this->_structure)) {
            $this->_fetchStructure();
        }
        
        return $this->_structure;
    }
    
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
        // Replace table prefix with config value
        $sql = str_replace('#__', config::DB_PREFIX, $sql);
        
        // Run SQL query
        try {
            // Prepare statement
            $stmt = $this->_pdo->prepare($sql);
            
            // Execute statement
            $stmt->execute($params);
            
            // Store reference to object in property
            $this->_stmt = $stmt;
            
            switch ($fetch_mode) {
                case self::FETCH_AFFECTED_ROWS :
                    return $this->_pdo->exec($sql);
            }
        }
        catch (PDOException $e) {
            throw new PHPFrame_Exception_Database('Query failed', $sql);
        }
        
        // If it is an INSERT query we return the insert id
        if (preg_match("/^INSERT/i", $sql)) {
            return $this->_pdo->lastInsertId();
        }
        
        return $stmt;
    }
    
    /**
     * Prepares a statement for execution and returns a statement object
     * 
     * @param string $statement This must be a valid SQL statement for the target 
     *                          database server.
     * @param array  $options   This array holds one or more key=>value pairs to 
     *                          set attribute values for the PDOStatement object 
     *                          that this method returns.
     * 
     * @access public
     * @return PDOStatement
     * @since  1.0
     */
    public function prepare($statement, $options=array())
    {
        $statement = str_replace('#__', config::DB_PREFIX, $statement);
        
        return $this->_pdo->prepare($statement, $options);
    }
    
    /**
     * Run query and load single result
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return mixed Returns a string with the single result or FALSE on failure.
     * @since  1.0
     */
    public function fetchColumn($sql, $params=array()) 
    {
        // Run SQL query
        $stmt = $this->query($sql, $params);
        
        // Fetch row and return
        return $stmt->fetchColumn();
    }
    
    /**
     * Run query and load single value for each row
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return mixed Returns an array containing single column for each row
     *               or FALSE on failure.
     * @since  1.0
     */
    public function fetchArray($sql, $params=array())
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        $rows = array();
        
        // Fetch associative array
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (is_array($row) && count($row) > 0) {
                $rows[] = $row[0];    
            }
        }
        
        return $rows;
    }
    
    public function fetchArrayList($sql, $params=array())
    {
        throw new Exception("FIX ME!!!");
    }
    
    /**
     * Run query and load single row as associative array
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     *
     * @access public
     * @return mixed Returns an associative array with the row data 
     *               or FALSE on failure.
     * @since  1.0
     */
    public function fetchAssoc($sql, $params=array()) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Run query and load single row as associative array
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     *
     * @access public
     * @return mixed Returns an associative array with the row data 
     *               or FALSE on failure.
     * @since  1.0
     */
    public function fetchAssocList($sql, $params=array()) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Run query and load single row as object
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return mixed Returns a row object or FALSE if query fails.
     * @since  1.0
     */
    public function fetchObject($sql, $params=array()) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        // Fetch row as object
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Run query and load array of row objects
     *
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return mixed An array of row objects or FALSE if query fails.
     * @since  1.0
     */
    public function fetchObjectList($sql, $params=array()) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        // Fetch all rows as objects
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get db escaped string
     * 
     * @param string $text The string to be escaped.
     * 
     * @access public
     * @return string Returns the escaped string.
     * @since  1.0
     */
    public function quote($text) 
    {
        return $this->_pdo->quote($text);
    }
    
    /**
     * Count total rows in a database table
     * 
     * @param string $table_name The name of the table we want to query.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function countRows($table_name) 
    {
        $query = "SELECT COUNT(id) FROM `".$table_name."`";
        return $this->loadResult($query);
    }
    
    /**
     * Close the current databasa connection
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function close() 
    {
        // unset PDO
        $this->_pdo = null;
    }
    
    /**
     * Create PDO object to represent db connection
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _connect() {
        try {
            // Acquire PDO object
            $this->_pdo = new PDO($this->_dsn, $this->_db_user, $this->_db_pass);
        }
        catch (PDOException $e) {
            //echo $e->getMessage();
            throw new PHPFrame_Exception_Database('Could not connect to database.');
        }  
    }
    
    /**
     * Fetch table structures from database
     * 
     * @access private
     * @return array
     * @since  1.0
     */
    private function _fetchStructure()
    {
        // First we check whether the data is already cached in the app registry
        $app_registry = PHPFrame::AppRegistry();
        $structure = $app_registry->get('database.structure');
        
        // Load structure from db if not in application registry already
        if (!isset($structure) || !is_array($structure)) {
            // Get list of all tables in database
            $sql = "show tables";
            $this->query($sql);
            
            // Loop through every table and read structure
            
            // Store structure in array uning table name as key
            
            // Return assoc array containing structure
                
            $sql = "SHOW COLUMNS FROM `".$table_name."`";
            
            $stmt = $this->_db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $error_info = $stmt->errorInfo();
            if (is_array($error_info) && count($error_info) > 1) {
                $exception_msg = "Couldn't read table structure for ";
                $exception_msg .= $table_name;
                throw new PHPFrame_Exception_Database($exception_msg, $error_info[2]);
            }
            
            // Add table structure to structures array
            $table_structures[$table_name] = $array;
            
            // Store data in app registry
            $app_registry->set('database.table_structures', $table_structures);
        }
        
        return $table_structures[$table_name];
    }
}
