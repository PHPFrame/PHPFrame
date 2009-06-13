<?php
/**
 * PHPFrame/Database/RowCollection.php
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
 * Row Collection Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        Iterator
 * @since      1.0
 */
class PHPFrame_Database_RowCollection implements Iterator
{
    /**
     * A reference to the DB connection to use for fetching rows
     * 
     * @var PHPFrame_Database
     */
    private $_db=null;
    /**
     * The SQL query that produced this collection
     * 
     * @var string
     */
    private $_query=null;
    /**
     * The name of the database table where this collection's rows are stored
     * 
     * @var string
     */
    private $_table_name=null;
    /**
     * The rows that make up the collection
     * 
     * @var array
     */
    private $_rows=array();
    /**
     * A pointer used to iterate through the rows array
     * 
     * @var int
     */
    private $_pos=0;
    
    /**
     * Constructor
     * 
     * @param string            $query The SQL query to load the collection of rows.
     * @param PHPFrame_Database $db    Optionally use an alternative database 
     *                                 to the default one provided by 
     *                                 PHPFrame::DB() as defined in config 
     *                                 class.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($query, PHPFrame_Database $db=null) 
    {
        $this->_query = (string) $query;
        
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
        
        // Get table name from query string
        $this->_fetchTableName($query);
        
        // Fetch rows from db
        $this->_fetchRows($query);
    }
    
    /**
     * Get rows in collection
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getRows() 
    {
        return $this->_rows;
    }
    
    /**
     * Get total number of rows in collection
     *   
     * @access public
     * @return int
     * @since  1.0
     */
    public function countRows() 
    {
        return count($this->_rows);
    }
    
    /**
     * Implementation of Iterator::current()
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function current() 
    {
        return $this->_rows[$this->_pos];
    }
    
    /**
     * Implementation of Iterator::next()
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function next() 
    {
        $this->_pos++;
    }
    
    /**
     * Implementation of Iterator::key()
     *   
     * @access public
     * @return int
     * @since  1.0
     */
    public function key() 
    {
        return $this->_pos;
    }
    
    /**
     * Implementation of Iterator::valid()
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function valid() 
    {
        return ($this->key() < $this->countRows());
    }
    
    /**
     * Implementation of Iterator::rewind()
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function rewind() 
    {
        $this->_pos = 0;
    }
    
    /**
     * Fetch table name from SQL query string
     * 
     * If unable to figure out the table name from the query string this method
     * will throw a PHPFrame_Exception with an exception code of E_PHPFRAME_NOTICE.
     * 
     * @param string $query SQL query containing the FROM clause from where to 
     *                      get the table name.
     * 
     * @access private
     * @return string
     * @since  1.0 
     */
    private function _fetchTableName($query) 
    {
        // Figure out table name from query
        $pattern = '/FROM ([a-zA-Z_\#]+)/';
        preg_match($pattern, $query, $matches);
        
        if (!isset($matches[1])) {
            $exception_msg = "Could not find collection table";
            $exception_code = PHPFrame_Exception::E_PHPFRAME_NOTICE;
            $exception_verbose = "Regular expression failed to find table in query.";
            $exception_verbose .= "Using pattern '".$pattern."'";
            $exception_verbose .= " on subject: '".$query."'";
            throw new PHPFrame_Exception($exception_msg, 
                                         $exception_code, 
                                         $exception_verbose);
        }
        
        $this->_table_name = (string) $matches[1];
    }
    
    /**
     * Run query and load array of row objects
     * 
     * @param string $query The query string used to fetch the rows from the db.
     *
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchRows($query) 
    {
        // Run SQL query
        $stmt = $this->_db->query($query);
        
        // Fetch associative array
        while ($array = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row = new PHPFrame_Database_Row($this->_table_name, $this->_db);
            $this->_rows[] = $row->bind($array);
        }
    }
}
