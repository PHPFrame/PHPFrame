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
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Database
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
     * @param PHPFrame_Database_DSN $dsn A Database Source Name object.
     * 
     * @return void
     * @access private
     * @since  1.0
     */
    private function __construct(PHPFrame_Database_DSN $dsn, $db_user=null, $db_pass=null) 
    {
        // Connect to database server
        try {
            $this->_pdo = new PDO($dsn, $db_user, $db_pass);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            throw new PHPFrame_Exception_Database('Could not connect to database.');
        }  
    }
    
    /**
     * Get Instance
     * 
     * @return PHPFrame_Database
     */
    public static function getInstance(PHPFrame_Database_DSN $dsn, $db_user=null, $db_pass=null) 
    {
        $key = $dsn->toString();
        if (!is_null($db_user)) {
            $key .= ";user=".$db_user;
        }
        if (!is_null($db_pass)) {
            $key .= ";pass=".$db_pass;
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
        //PHPFrame_Debug_Log::write($this->_query);
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
     * Run query and load single result
     * 
     * @access    public
     * @return    mixed    Returns a string with the single result or FALSE on failure.
     * @since    1.0
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
     * @access    public
     * @return     mixed    Returns an array containing single column for each row or FALSE on failure.
     * @since    1.0
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
     * @access    public
     * @return    mixed    Returns a row object or FALSE if query fails.
     * @since    1.0
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
     * @param string $sql
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
     * @access    public
     * @return    mixed    Returns an associative array with the row data or FALSE on failure.
     * @since    1.0
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
     * @access    public
     * @param    string    The string to be escaped.
     * @return    string    Returns the escaped string.
     * @since    1.0
     */
    public function getEscaped($text) 
    {
        
        $result = $this->_pdo->quote($text);
        
        return $result;
    }
    
    public function countRows($table_name) 
    {
        $query = "SELECT COUNT(id) FROM `".$table_name."`";
        return $this->loadResult($query);
    }
    
    /**
     * Close the current databasa connection
     * 
     */
    public function close() 
    {
        // unset PDO
        $this->_pdo = null;
    }
}
