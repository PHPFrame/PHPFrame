<?php
/**
 * PHPFrame/Database/Database.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Database
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * This class deals with the connection(s) to the database(s).
 *
 * The database class serves singleton objects for each connection, determined
 * by the dsn and db user credentials.
 *
 * This class also extends {@link PHPFrame_Subject} allowing observer objects
 * to subscribe for updates.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Subject, PHPFrame_DatabaseTable, PHPFrame_DatabaseColumn
 * @since    1.0
 * @abstract
 */
abstract class PHPFrame_Database extends PHPFrame_Subject
{
    /**
     * Return query() result as single primitive value
     *
     * @var int
     */
    const FETCH_COLUMN = 0;
    /**
     * Return query() result as list of single primitive values
     *
     * @var int
     */
    const FETCH_COLUMN_LIST = 1;
    /**
     * Return query() result as numerically indexed array
     *
     * @var int
     */
    const FETCH_ARRAY = 2;
    /**
     * Return query() result as a list of numerically indexed arrays
     *
     * @var int
     */
    const FETCH_ARRAY_LIST = 3;
    /**
     * Return query() result as associative array
     *
     * @var int
     */
    const FETCH_ASSOC = 4;
    /**
     * Return query() result as a list of associative arrays
     *
     * @var int
     */
    const FETCH_ASSOC_LIST = 5;
    /**
     * Return query() result as object
     *
     * @var int
     */
    const FETCH_OBJ = 6;
    /**
     * Return query() result as a list of objects
     *
     * @var int
     */
    const FETCH_OBJ_LIST = 7;
    /**
     * Return query() result as number of rows returned
     *
     * @var int
     */
    const FETCH_NUM_ROWS = 8;
    /**
     * Return query() result as number of records affected
     *
     * @var int
     */
    const FETCH_AFFECTED_ROWS = 9;
    /**
     * Return query() result as last insert id
     *
     * @var int
     */
    const FETCH_LAST_INSERTID = 10;
    /**
     * Return PDOStatement object from query() method
     *
     * @var int
     */
    const FETCH_STMT = 11;

    /**
     * An array holding instances of this class
     *
     * @var array containing objects of type PHPFrame_Database
     */
    private static $_instances = array();
    /**
     * DSN used for the database connection
     *
     * @var string
     */
    protected $dsn = null;
    /**
     * Database user if any
     *
     * @var string
     */
    protected $db_user = null;
    /**
     * Database password if any
     *
     * @var string
     */
    protected $db_pass = null;
    /**
     * A PDO object that represents the connection to the database server.
     *
     * @var PDO
     */
    private $_pdo = null;
    /**
     * A reference to the PDOStatement object from the last query
     *
     * @var PDOStatement
     */
    private $_stmt = null;
    /**
     * Table prefix
     *
     * @var string
     */
    private $_tbl_prefix = "";

    /**
     * Constructor
     *
     * The constructor connects to the MySQL server and selects the database.
     *
     * @param string $dsn        A database source name
     * @param string $db_user    [Optional] The database user name if any.
     * @param string $db_pass    [Optional] The database password if any.
     * @param string $tbl_prefix [Optional] Table prefix.
     *
     * @return void
     * @since  1.0
     */
    private function __construct(
        $dsn,
        $db_user=null,
        $db_pass=null,
        $tbl_prefix=null
    ) {
        // Set internal properties
        $this->dsn     = $dsn;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;

        if (!is_null($tbl_prefix)) {
            $this->_tbl_prefix = trim((string) $tbl_prefix);
        }

        // Connect to database server
        $this->connect();
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

        return array("dsn", "db_user", "db_pass");
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
        $this->connect();
    }

    /**
     * Get Instance
     *
     * @param string $dsn        A database source name.
     * @param string $db_user    [Optional] The database user name if any.
     * @param string $db_pass    [Optional] The database password if any.
     * @param string $tbl_prefix [Optional] Table prefix.
     *
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function getInstance(
        $dsn,
        $db_user=null,
        $db_pass=null,
        $tbl_prefix=null
    ) {
        $dsn = trim((string) $dsn);

        if (!empty($db_user)) {
            $dsn .= ";user=".$db_user;
        }

        if (preg_match('/^(mysql|sqlite)/i', $dsn, $matches)) {
            $driver = strtolower($matches[1]);
            switch ($driver) {
            case "sqlite" :
                $concrete_class = "PHPFrame_SQLiteDatabase";
                break;
            case "mysql" :
                $concrete_class = "PHPFrame_MySQLDatabase";
                break;
            }
        } else {
            $msg = "Database driver not recognised.";
            throw new PHPFrame_DatabaseException($msg);
        }

        if (!isset(self::$_instances[$dsn])) {
            self::$_instances[$dsn] = new $concrete_class(
                $dsn,
                $db_user,
                $db_pass,
                $tbl_prefix
            );
        }

        return self::$_instances[$dsn];
    }

    /**
     * Create PDO object to represent db connection
     *
     * @return void
     * @since  1.0
     */
    public function connect()
    {
        try {
            // Acquire PDO object
            $this->_pdo = new PDO(
                $this->dsn,
                $this->db_user,
                $this->db_pass
            );
        } catch (PDOException $e) {
            throw new PHPFrame_DatabaseException($e->getMessage());
        }
    }

    /**
     * Get the PDO object
     *
     * @return PDO
     * @since  1.0
     */
    protected function getPDO()
    {
        return $this->_pdo;
    }

    /**
     * Check whether database has a given table.
     *
     * @param string $table_name The name of the table to check.
     *
     * @return bool
     * @since  1.0
     */
    public function hasTable($table_name)
    {
        // Replace table prefix
        $table_name = str_replace('#__', $this->_tbl_prefix, $table_name);

        foreach ($this->getTables(true) as $table) {
            if ($table == $table_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the database tables.
     *
     * @param bool $return_names [Optional] Default value is FALSE. If set to
     *                           TRUE an array containing table names will be
     *                           returned instead of an array containing
     *                           objects of type {@link PHPFrame_DatabaseTable}.
     *
     * @return array
     * @since  1.0
     */
    abstract public function getTables($return_names=false);

    /**
     * Create database table for a given table object
     *
     * @param PHPFrame_DatabaseTable $table A reference to an object of type
     *                                      PHPFrame_DatabaseTable representing
     *                                      the table we want to create.
     *
     * @return void
     * @since  1.0
     */
    abstract public function createTable(PHPFrame_DatabaseTable $table);

    /**
     * Delete a named database table
     *
     * @param string $table_name The name of the table we want to drop.
     *
     * @return void
     * @since  1.0
     */
    public function dropTable($table_name)
    {
        $sql = "DROP TABLE IF EXISTS `".$table_name."`";

        // Run SQL query
        $this->query($sql);
    }

    /**
     * Alter a database table
     *
     * @param PHPFrame_DatabaseTable $table A reference to an object of type
     *                                      PHPFrame_DatabaseTable representing
     *                                      the table we want to alter.
     *
     * @return void
     * @since  1.0
     */
    abstract public function alterTable(PHPFrame_DatabaseTable $table);

    /**
     * Truncate a database table. This method deletes all records from a table
     * and resets the auto increment counter back to zero.
     *
     * @param string $table_name The name of the table we want to truncate.
     *
     * @return void
     * @since  1.0
     */
    abstract public function truncate($table_name);

    /**
     * Get the columns of a given table
     *
     * @param string $table_name The name of the table for which we want to get
     *                           the columns.
     *
     * @return array
     * @since  1.0
     */
    abstract public function getColumns($table_name);

    /**
     * Get the PDOStatement object
     *
     * @return PDOStatement
     * @since  1.0
     */
    public function getStatement()
    {
        return $this->_stmt;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement
     * object.
     *
     * @param string $sql        The SQL statement to run
     * @param array  $params     An array with the query parameters if any
     * @param int    $fetch_mode Mode in which to fetch the query result
     *
     * @return mixed
     * @since  1.0
     */
    public function query($sql, $params=array(), $fetch_mode=self::FETCH_STMT)
    {
        // Replace table prefix
        $sql = str_replace('#__', $this->_tbl_prefix, $sql);

        // Run SQL query
        try {
            // Prepare statement
            $this->_stmt = $this->_pdo->prepare($sql);

            if (!($this->_stmt instanceof PDOStatement)) {
                $msg = implode("\n", $this->_pdo->errorInfo());
                throw new PHPFrame_DatabaseException($msg);
            }
            // Execute statement
            $this->_stmt->execute($params);

            $error_info = $this->_stmt->errorInfo();
            if (is_array($error_info)
                && count($error_info) > 1
                && (int) $error_info[0] > 0
            ) {
                $msg = "Error running query";
                throw new PHPFrame_DatabaseException($msg, $this->_stmt);
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
            throw new PHPFrame_DatabaseException(
                'Query failed',
                $this->getStatement()
            );
        }
        catch (PHPFrame_DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $statement This must be a valid SQL statement for the
     *                          target database server.
     * @param array  $options   This array holds one or more key=>value pairs
     *                          to set attribute values for the PDOStatement
     *                          object that this method returns.
     *
     * @return PDOStatement
     * @since  1.0
     */
    public function prepare($statement, $options=array())
    {
        $statement = str_replace('#__', $this->_tbl_prefix, $statement);

        return $this->_pdo->prepare($statement, $options);
    }

    /**
     * Get id from the last insert query
     *
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
     * @since  1.0
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
     * @return void
     * @since  1.0
     */
    public function close()
    {
        // unset PDO
        $this->_pdo = null;
    }

    /**
     * Is this instance of type SQLite?
     *
     * @return bool
     * @since  1.0
     */
    public function isSQLite()
    {
        return ($this instanceof PHPFrame_SQLiteDatabase);
    }

    /**
     * Is this instance of type MySQL?
     *
     * @return bool
     * @since  1.0
     */
    public function isMySQL()
    {
        return ($this instanceof PHPFrame_MySQLDatabase);
    }
}
