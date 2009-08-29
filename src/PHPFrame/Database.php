<?php
/**
 * PHPFrame/Database.php
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
 * Database class
 * 
 * This class deals with the connection(s) to the database(s).
 * 
 * The database class serves singleton objects for each connection, determined by 
 * the dsn and db user credentials.
 * 
 * This class also extends PHPFrame_Base_Subject allowing observer objects to 
 * subscribe for updates.
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Base_Subject
 * @since    1.0
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
     * Return query() result as list of single primitive values
     * 
     * @var int
     */
    const FETCH_COLUMN_LIST=1;
    /**
     * Return query() result as numerically indexed array
     * 
     * @var int
     */
    const FETCH_ARRAY=2;
    /**
     * Return query() result as a list of numerically indexed arrays
     * 
     * @var int
     */
    const FETCH_ARRAY_LIST=3;
    /**
     * Return query() result as associative array
     * 
     * @var int
     */
    const FETCH_ASSOC=4;
    /**
     * Return query() result as a list of associative arrays
     * 
     * @var int
     */
    const FETCH_ASSOC_LIST=5;
    /**
     * Return query() result as object
     * 
     * @var int
     */
    const FETCH_OBJ=6;
    /**
     * Return query() result as a list of objects
     * 
     * @var int
     */
    const FETCH_OBJ_LIST=7;
    /**
     * Return query() result as number of rows returned
     * 
     * @var int
     */
    const FETCH_NUM_ROWS=8;
    /**
     * Return query() result as number of records affected
     * 
     * @var int
     */
    const FETCH_AFFECTED_ROWS=9;
    /**
     * Return query() result as last insert id
     * 
     * @var int
     */
    const FETCH_LAST_INSERTID=10;
    /**
     * Return PDOStatement object from query() method
     * 
     * @var int
     */
    const FETCH_STMT=11;
    
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
        $this->_dsn     = $dsn;
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
        // Disconnect from database server by unsetting PDO object
        $this->_pdo = null;
        
        // Remove reference to PDOStatement object 
        $this->_stmt = null;
        
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
     * Get the database structure
     * 
     * @param string $table_name Optional parameter to specify a given table.
     *                           If omitted the whole db structure is returned.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getStructure($table_name=null)
    {
        // Replace table prefix with config value
        $table_name = str_replace('#__', PHPFrame::Config()->get("db.prefix"), $table_name);
        
        if (!is_array($this->_structure) || count($this->_structure) < 1) {
            $this->_fetchStructure();
        }
        
        if (!is_null($table_name)) {
            if (!isset($this->_structure[$table_name])) {
                $msg = "Cound not get table structure";
                throw new PHPFrame_Exception_Database($msg);
            }
            
            return $this->_structure[$table_name];
        }
        
        return $this->_structure;
    }
    
    /**
     * Get the PDOStatement object
     * 
     * @access public
     * @return PDOStatement
     * @since  1.0
     */
    public function getStatement()
    {
        return $this->_stmt;
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
        $sql = str_replace('#__', PHPFrame::Config()->get("db.prefix"), $sql);
        
        // Run SQL query
        try {
            // Prepare statement
            $this->_stmt = $this->_pdo->prepare($sql);
            
            if (!($this->_stmt instanceof PDOStatement)){
                $msg = implode("\n", $this->_pdo->errorInfo());
                throw new PHPFrame_Exception_Database($msg);
            }
            // Execute statement
            $this->_stmt->execute($params);
            
            $error_info = $this->_stmt->errorInfo();
            if (is_array($error_info) && count($error_info) > 1) {
                $msg = "Error running query";
                throw new PHPFrame_Exception_Database($msg, 
                                                      PHPFrame_Exception::ERROR, 
                                                      $this->_stmt);
            }
            
            switch ($fetch_mode) {
                case self::FETCH_COLUMN :
                    return $this->_stmt->fetchColumn();
                    
                case self::FETCH_COLUMN_LIST :
                    return $this->_stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                case self::FETCH_ARRAY :
                    return $this->_stmt->fetch(PDO::FETCH_NUM);
                    
                case self::FETCH_ARRAY_LIST :
                    return $this->_stmt->fetchAll(PDO::FETCH_NUM);
                    
                case self::FETCH_ASSOC :
                    return $this->_stmt->fetch(PDO::FETCH_ASSOC);
                    
                case self::FETCH_ASSOC_LIST :
                    return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                case self::FETCH_OBJ :
                    return $this->_stmt->fetch(PDO::FETCH_OBJ);
                    
                case self::FETCH_OBJ_LIST :
                    return $this->_stmt->fetchAll(PDO::FETCH_OBJ);
                    
                case self::FETCH_NUM_ROWS :
                    return $this->_stmt->rowCount();
                    
                case self::FETCH_AFFECTED_ROWS :
                    //return $this->_pdo->exec($sql);
                    
                case self::FETCH_LAST_INSERTID :
                    return $this->lastInsertId();
                    
                default :
                    return $this->_stmt;
            }
        }
        catch (PDOException $e) {
            throw new PHPFrame_Exception_Database('Query failed', 
                                                  PHPFrame_Exception::ERROR, 
                                                  $this->_stmt);
        }
        catch (PHPFrame_Exception_Database $e) {
            throw $e;
        }
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
        $statement = str_replace('#__', PHPFrame::Config()->get("db.prefix"), $statement);
        
        return $this->_pdo->prepare($statement, $options);
    }
    
    /**
     * Get id from the last insert query
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function lastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }
    
    /**
     * Run query and fetch single result
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return string Returns a string with the single result.
     * @since  1.0
     */
    public function fetchColumn($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_COLUMN);
    }
    
    /**
     * Run query and fetch a list of single values per row
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return array Returns an array with strings with the single result.
     * @since  1.0
     */
    public function fetchColumnList($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_COLUMN_LIST);
    }
    
    /**
     * Run query and fetch single row as a numerically indexed array
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return array Returns an array containing single column for each row
     *               or FALSE on failure.
     * @since  1.0
     */
    public function fetchArray($sql, $params=array())
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_ARRAY);
    }
    
    /**
     * Run query and fetch a numerically indexed array containing numerically
     * indexed arrays with the row data
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @return array A numerically indexed array containing numerically indexed
     *               arrays with the row data
     */
    public function fetchArrayList($sql, $params=array())
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_ARRAY_LIST);
    }
    
    /**
     * Run query and fetch single row as associative array
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     *
     * @access public
     * @return array An associative array with the row data
     * @since  1.0
     */
    public function fetchAssoc($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_ASSOC);
    }
    
    /**
     * Run query and fetch rows as associative arrays
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     *
     * @access public
     * @return array A numerically indexed array containing associative arrays
     *               with the row data
     * @since  1.0
     */
    public function fetchAssocList($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_ASSOC_LIST);
    }
    
    /**
     * Run query and fetch single row as object
     * 
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return stdClass
     * @since  1.0
     */
    public function fetchObject($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_OBJ);
    }
    
    /**
     * Run query and fetch array of row objects
     *
     * @param string $sql    The SQL statement to run.
     * @param array  $params An array with the query parameters if any
     * 
     * @access public
     * @return array An array containing objects of type stdClass
     * @since  1.0
     */
    public function fetchObjectList($sql, $params=array()) 
    {
        // Delegate to main query method
        return $this->query($sql, $params, self::FETCH_OBJ_LIST);
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
        $sql = "SELECT COUNT(id) FROM `".$table_name."`";
        return $this->fetchColumn($sql);
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
            if ($this->_dsn instanceof PHPFrame_Database_DSN_MySQL){
                $this->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
        }
        catch (PDOException $e) {
            throw new PHPFrame_Exception_Database($e->getMessage());
        }  
    }
    
    /**
     * Fetch table structures from database
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchStructure()
    {
        // First we check whether the data is already cached in the app registry
        $app_registry = PHPFrame::AppRegistry();
        $structure = $app_registry->get('database.structure.'.$this->_key);
        
        // Load structure from db if not in application registry already
        if (!isset($structure) || !is_array($structure)) {
            // Get list of all tables in database
            $sql = "show tables";
            $tables = $this->fetchColumnList($sql);
            
            // Loop through every table and read structure
            foreach ($tables as $table_name) {
                // Store structure in array uning table name as key
                $sql = "SHOW COLUMNS FROM `".$table_name."`";
                $structure[$table_name] = $this->fetchAssocList($sql);
            }
            
            // Store data in app registry
            $app_registry->set('database.structure'.$this->_key, $structure);
        }
        
        $this->_structure = $structure;
    }
}
