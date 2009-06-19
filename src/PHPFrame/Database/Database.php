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
     * @access private
     * @var    PDO
     */
    private $_pdo=null;
    
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
     * Executes an SQL statement, returning a result set as a PDOStatement object 
     * 
     * @param string $sql The SQL statement to run 
     * 
     * @access public
     * @return PDOStatement
     * @since  1.0
     */
    public function query($sql) 
    {
        $sql = str_replace('#__', config::DB_PREFIX, $sql);
        
        // Run SQL query
        try {
            $stmt = $this->_pdo->query($sql);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            throw new PHPFrame_Exception_Database('Query failed', $sql);
        }
        
        // If it is an INSERT query we return the insert id
        if (stripos($sql, 'INSERT') !== false) {
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
     * @param string $sql The SQL statement to run.
     * 
     * @access public
     * @return mixed Returns a string with the single result or FALSE on failure.
     * @since  1.0
     */
    public function loadResult($sql) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        // Fetch row and return
        return $stmt->fetchColumn();
    }
    
    /**
     * Run query and load single value for each row
     * 
     * @param string $sql The SQL statement to run.
     * 
     * @access public
     * @return mixed Returns an array containing single column for each row
     *               or FALSE on failure.
     * @since  1.0
     */
    public function loadResultArray($sql) 
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
    
    /**
     * Run query and load single row as object
     * 
     * @param string $sql The SQL statement to run.
     * 
     * @access public
     * @return mixed Returns a row object or FALSE if query fails.
     * @since  1.0
     */
    public function loadObject($sql) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        // Fetch row as object
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Run query and load array of row objects
     *
     * @param string $sql The SQL statement to run.
     * 
     * @access public
     * @return mixed An array of row objects or FALSE if query fails.
     * @since  1.0
     */
    public function loadObjectList($sql) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        // Fetch all rows as objects
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Run query and load single row as associative array
     * 
     * @param string $sql The SQL statement to run.
     *
     * @access public
     * @return mixed Returns an associative array with the row data 
     *               or FALSE on failure.
     * @since  1.0
     */
    public function loadAssoc($sql) 
    {
        // Run SQL query
        $stmt = $this->query($sql);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
    public function getEscaped($text) 
    {
        $result = $this->_pdo->quote($text);
        
        return $result;
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
     * @return void
     * @since  1.0
     */
    private function _connect() {
        try {
            $this->_pdo = new PDO($this->_dsn, $this->_db_user, $this->_db_pass);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            throw new PHPFrame_Exception_Database('Could not connect to database.');
        }  
    }
}
