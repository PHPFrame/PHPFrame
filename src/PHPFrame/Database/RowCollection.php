<?php
/**
 * PHPFrame/Database/RowCollection.php
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
 * Row Collection Class
 * 
 * This class is used to handle collections of rows from the database.
 * 
 * RowCollection objects are "iterable" for ease of use and also provide standard
 * pagination.
 * 
 * It provides a mechanism to build a SQL query used to fetch the row data.
 * This mechanism is delegated to the IdObject.
 * 
 * Note that prepared statements and parameter markers are allowed. Using parameter
 * markers for input data is strongly encouraged for security.
 * 
 * Example 1:
 * 
 * <code>
 * // Create RowCollection object
 * $rows = new PHPFrame_Database_RowCollection(array("select"=>"email",
 *                                                   "from"=>"#__users",
 *                                                   "where"=>array("id", "=", "62")
 *                                                   )
 *                                             );
 * // Load the selection
 * $rows->load();
 * // Print results
 * foreach ($rows as $row) {
 *     echo $row->email;
 * }
 * </code>
 * 
 * Example 2 using separate options array for clarity:
 * 
 * Note the use of parameter marker.
 * 
 * <code>
 * // Create options array
 * $options = array("select"=>"*", 
 *                  "from"=>"#__users",
 *                  "where"=>array("id", "=", ":id")
 *                  "params"=>array(":id", 62));
 * 
 * // Create RowCollection object
 * $rows2 = new PHPFrame_Database_RowCollection($options);
 * // Load the selection
 * $rows2->load();
 * // Print results
 * foreach ($rows2 as $row) {
 *     echo $row->firstname." ".$row->firstname." <".$row->email.">";
 * }
 * </code>
 * 
 * Example 3 using id object's fluent syntax and parameter markers:
 * 
 * <code>
 * // Create RowCollection object
 * $rows3 = new PHPFrame_Database_RowCollection();
 * // Make a selection
 * $rows3->select("*")
 *       ->from("#__users")
 *       ->where("id", "=", ":id")
 *       ->params(":id", 62);
 * // Load the selection
 * $rows3->load();
 * // Print results
 * print_r($rows3);
 * </code>
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Database_IdObject, PHPFrame_Database
 * @since      1.0
 * @deprecated
 * @see PHPFrame_Mapper
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
     * An id object held internally to load rows from db
     * 
     * @var PHPFrame_Database_IdObject
     */
    private $_id_obj=null;
    /**
     * The total number of entries for selected table. This includes entries that
     * fall outside the current subset/page when using limits.
     * 
     * @var int
     */
    private $_total=null;
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
     * @param array             $options An array with initialisation options.
     * @param PHPFrame_Database $db      Optionally use an alternative database 
     *                                   to the default one provided by 
     *                                   PHPFrame::DB() as defined in config 
     *                                   class.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($options=null, PHPFrame_Database $db=null)
    {
        // Handle options
        
        // If no database object is passed we use default connection
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
        
        // Acquire id object used to handle SQL query
        $this->_id_obj = new PHPFrame_Database_IdObject($options);
    }
    
    /**
     * Get available options
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getOptions()
    {
        // Return options from Id Object
        return $this->_id_obj->getOptions();
    }
    
    /**
     * Convert row collection to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        for ($i=0; $i<count($this->_rows); $i++) {
            // Add table headings
            if ($i == 0) {
                $fields = $this->_id_obj->getSelectFields();
                // If using "*" we get all fields in table from row object
                if (is_array($fields) && $fields[0] == "*") {
                    $fields = $this->getKeys();
                }
                
                foreach ($fields as $key) {
                    $str .= PHPFrame_Base_String::fixLength($key, 16)."\t";
                }
                $str .= "\n";
            }
            
            $str .= $this->_rows[$i]->toString(false)."\n";
        }
        
        return $str;
    }
    
    /**
     * Set the fields array used in select statement
     * 
     * @param string|array $fields a string or array of strings with field names
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function select($fields)
    {
        $this->_id_obj->select($fields);
        
        return $this;
    }
    
    /**
     * Set the table from which to select rows
     * 
     * @param string $table A string with the table name
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function from($table)
    {
        $this->_id_obj->from($table);
        
        return $this;
    }
    
    /**
     * Add a join clause to the select statement
     * 
     * @param sting $join A join statement
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function join($join)
    {
        $this->_id_obj->join($join);
        
        return $this;
    }
    
    /**
     * Add "where" condition
     * 
     * @param string $left
     * @param string $operator
     * @param string $right
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function where($left, $operator, $right)
    {
        $this->_id_obj->where($left, $operator, $right);
        
        return $this;
    }
    
    /**
     * Set group by clause
     * 
     * @param string $column The column name to group by
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function groupby($column)
    {
        $this->_id_obj->groupby($column);
        
        return $this;
    }
    
    /**
     * Set order by clause
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function orderby($column, $direction=null)
    {
        $this->_id_obj->orderby($column, $direction);
        
        return $this;
    }
    
    /**
     * Set order direction
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function orderdir($direction)
    {
        $this->_id_obj->orderdir($direction);
        
        return $this;
    }
    
    /**
     * Set limit clause
     * 
     * @param int $limit     The total number of entries we want to limit to
     * @param int $limistart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function limit($limit, $limistart=null)
    {
        $this->_id_obj->limit($limit, $limistart);
        
        return $this;
    }
    
    /**
     * Set row number of first row in current page
     * 
     * @param int $limistart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function limistart($limistart)
    {
        $this->_id_obj->limistart($limistart);
 
        return $this;
    }
    
    /**
     * Set query parameters
     * 
     * @param string $key
     * @param string $value
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function params($key, $value)
    {
        $this->_id_obj->params($key, $value);
 
        return $this;
    }
    
    /**
     * Load rows from database
     * 
     * @param PHPFrame_Database_IdObject $id_object
     * @param string                     $exclude      A list of key names to 
     *                                                 exclude from row binding
     *                                                 process separated by commas.
     * 
     * @access public
     * @return PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function load(PHPFrame_Database_IdObject $id_object=null, $exclude='')
    {
        if ($id_object instanceof PHPFrame_Database_IdObject) {
            $this->_id_obj = $id_object;
        }
        
        $this->_fetchRows($this->_id_obj, $exclude);
        $this->_fetchTotalNumRows($this->_id_obj);
        
        return $this;
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
     * Get fields names for rows
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getKeys()
    {
        if (count($this->_rows) == 0) {
            return null;
        }
        
        return $this->_rows[0]->getFields();
    }
    
    public function getSQL()
    {
        return (string) $this->_id_obj;
    }
    
    /**
     * Get number of records the current page/subset is limited to
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimit()
    {
        return $this->_id_obj->getLimit();
    }
    
    /**
     * Get row number from where the current page start
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimitstart()
    {
        return $this->_id_obj->getLimitstart();
    }
    
    /**
     * Get total number of records in db table ignoring the limit clause
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getTotal()
    {
        return $this->_total;
    }
    
    /**
     * Get number of pages
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getPages() 
    {
        if ($this->getLimit() > 0 && $this->getTotal() > 0) {
            // Calculate number of pages
            return (int) ceil($this->getTotal()/$this->getLimit());
        } else {
            return 0;
        }
    }
    
    /**
     * Get current page number
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getCurrentPage() 
    {
        // Calculate current page
        if ($this->getLimit() > 0) {
            return (int) (ceil($this->getLimitstart()/$this->getLimit())+1);
        } else {
            return 0;
        }
    }
    
    /**
     * Get number of rows in collection subset/page
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
     * Load array of row objects using id object
     * 
     * @param PHPFrame_Database_IdObject $id_object    The id object used to generate
     *                                                 the query.
     * @param string                     $exclude      A list of key names to 
     *                                                 exclude from row binding
     *                                                 process separated by commas.
     *
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchRows(PHPFrame_Database_IdObject $id_object, $exclude='')
    {
        // Cast Id Object to string (this produces a SQL query)
        $sql = (string) $id_object;
        
        // Run SQL query
        $stmt = $this->_db->prepare($sql);
        
        if (!($stmt instanceof PDOStatement)) {
            $msg = "Could not load rows from database.";
            throw new PHPFrame_Exception_Database($msg);
        } else {
            // Fetch associative array
            $stmt->execute($this->_id_obj->getParams());
            //var_dump($this->_id_obj->getParams()); exit;
            $table_name = $this->_id_obj->getTableName();
            while ($array = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row = new PHPFrame_Database_Row($table_name, $this->_db);
                $this->_rows[] = $row->bind($array, $exclude);
            }
        }
    }
    
    /**
     * Fetch the total number of rows in the db table
     * 
     * This method ignores the 
     * 
     * @param string $id_object The id object used to generate the query.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    private function _fetchTotalNumRows(PHPFrame_Database_IdObject $id_obj)
    {
        // Convert Id Object to SQL string without limits
        $sql = $id_obj->getSQL(false);
        
        // Run SQL query
        $stmt = $this->_db->prepare($sql);
        
        if (!($stmt instanceof PDOStatement)) {
            $msg = "Could not count rows from database.";
            throw new PHPFrame_Exception_Database($msg);
        } else {
            // Fetch row count
            $stmt->execute($this->_id_obj->getParams());
            $this->_total = $stmt->rowCount();
        }
    }
}
