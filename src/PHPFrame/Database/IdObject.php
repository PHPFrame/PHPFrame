<?php
/**
 * PHPFrame/Database/IdObject.php
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
 * IdObject class
 * 
 * This class encapsulates the selection of rows from the database.
 * 
 * IdObjects are used by both "Row" and "RowCollection" objects in order to build
 * SQL queries. Both of this "database facing" classes make use of the IdObject by
 * means of object composition, providing added flexibility.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Database_RowCollection
 * @since      1.0
 */
class PHPFrame_Database_IdObject
{
    /**
     * An array with the columns to get in SELECT statement
     * 
     * @var array
     */
    private $_select=array();
    /**
     * The name of the table this collection represents
     * 
     * @var string
     */
    private $_from=null;
    /**
     * Array containing joins
     * 
     * @var array
     */
    private $_join=array();
    /**
     * An array containing conditions for the SQL WHERE clause
     * 
     * @var string
     */
    private $_where=array();
    /**
     * String containing the group by clause
     * 
     * @var string
     */
    private $_groupby=null;
    /**
     * Column to use for ordering
     * 
     * @var string
     */
    private $_orderby=null;
    /**
     * Column to use for ordering
     * 
     * @var string
     */
    private $_orderdir="ASC";
    /**
     * Number of rows per page
     * 
     * @var int
     */
    private $_limit=-1;
    /**
     * Row number from where the current page start
     * 
     * @var int
     */
    private $_limitstart=0;
    /**
     * Input parameters used in prepared statements.
     * 
     * @var array
     */
    private $_params=array();
    
    /**
     * Constructor
     * 
     * @param array $options An associative array with initialisation options.
     *                       For a list of available options invoke 
     *                       PHPFrame_Database_IdObject::getOptions().
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($options=null)
    {
        // Process input options
        if (!is_null($options)) {
            if (!PHPFrame_Base_Array::isAssoc($options)) {
                $msg = "Options passed in wrong format.";
                $msg .= " Options should be passed as an associative";
                $msg .= " array with key value pairs.";
                throw new PHPFrame_Exception_Database($msg);
            }
            
            // Options is an array
            foreach ($options as $key=>$val) {
                if (method_exists($this, $key)) {
                    call_user_func_array(array($this, $key), $val);
                }
            }
        }
    }
    
    /**
     * Magic method invoked when trying to use object as string.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->getSQL();
    }
    
    /**
     * Return an array with the list of available options in this object.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getOptions()
    {
        $raw_keys = array_keys(get_object_vars($this));
        
        // Remove preceding underscore from property names
        foreach ($raw_keys as $key) {
            $keys[] = substr($key, 1);
        }
        
        return $keys;
    }
    
    /**
     * Set the fields array used in select statement
     * 
     * This method takes either a string a single column name or an array of column
     * names.
     * 
     * The "*" character is allowed and used to select "all" columns.
     * 
     * This method returns the IdObject object, making it possible to use "fluent syntax".
     * 
     * Example:
     * 
     * <code>
     * // Select all columns from table "my_table"
     * $id_object = new PHPFrame_Database_IdObject();
     * $id_object->select("*")->from("my_table");
     * // echo the SQL, this will automatically cast the IdObject to string
     * echo $id_object;
     * 
     * 
     * // The same as above but passing the "select" and "table" options to the constructor.
     * $options = array("select"=>"*", "from"->"my_table");
     * $id_object = new PHPFrame_Database_IdObject($options);
     * // echo the SQL, this will automatically cast the IdObject to string
     * echo $id_object;
     * 
     * // Now we create a new IdObject and select only some specified fields
     * $id_object = new PHPFrame_Database_IdObject();
     * $id_object->select(array("id", "name", "email"))->from("my_table");
     * </code>
     * 
     * @param string|array $fields a string or array of strings with field names
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function select($fields)
    {
        // Validate input type and set internal property
        $pattern = "/^[a-zA-Z_ \.\*\(\)\#]+$/";
        $fields = PHPFrame_Utils_Filter::validateRegExp($fields, $pattern);
        
        if (is_string($fields)) {
            $fields = array($fields);
        }
        
        $this->_select = $fields;
        
        return $this;
    }
    
    /**
     * Set the table from which to select rows
     * 
     * This method supports only one table in the from clause. 
     * Please use the join() method to add join tables.
     * 
     * Tables may be passed with an alias. Ie: "table_name AS tn".
     * 
     * @param string $table A string with the table name
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function from($table)
    {
        // Check if input contaings alias
        preg_match("/([a-zA-Z_\#\.]+) (as) ([a-zA-Z_]+)/i", $table, $matches);
        if (count($matches) == 4) {
            $table = array($matches[1], $matches[3]);
        }
        
        // Validate input type and set internal property
        $pattern = "/^[a-zA-Z_\#\.]+$/";
        $this->_from = PHPFrame_Utils_Filter::validateRegExp($table, $pattern);
        
        return $this;
    }
    
    /**
     * Add a join clause to the select statement
     * 
     * @param sting $join A join statement
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function join($join)
    {
        $join = (string) trim($join);
        // Validate input type and set internal property
        $pattern = "/^([a-zA-Z]*[ ]{0,1}JOIN) ([a-zA-Z_\#]+) ([a-zA-Z]+) ON ([a-zA-Z]+\.[a-zA-Z_]+) (=) ([a-zA-Z]+\.[a-zA-Z_]+)/";
        preg_match($pattern, $join, $matches);
        
        $this->_join[] = array("type"=>$matches[1],
                               "table_name"=>$matches[2],
                               "table_alias"=>$matches[3],
                               "on"=>array($matches[4], $matches[5], $matches[6]));
        
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
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function where($left, $operator, $right)
    {
        // Validate input types and set internal property
        $pattern = "/^[a-zA-Z0-9_= \-\#\.\(\)\'\%\:]+$/";
        $left = PHPFrame_Utils_Filter::validateRegExp($left, $pattern);
        $right = PHPFrame_Utils_Filter::validateRegExp($right, $pattern);
        // Validate operators
        $pattern = "/^(=|<|>|<=|>=|AND|OR|LIKE|BETWEEN)$/";
        $operator = PHPFrame_Utils_Filter::validateRegExp($operator, $pattern);
        
        $this->_where[] = array($left, $operator, $right);
        
        return $this;
    }
    
    /**
     * Set group by clause
     * 
     * @param string $column The column name to group by
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function groupby($column)
    {
        // Validate input type and set internal property
        $pattern = "/^[a-zA-Z_ \#\.]+$/";
        $this->_groupby = PHPFrame_Utils_Filter::validateRegExp($column, $pattern);
        
        return $this;
    }
    
    /**
     * Set order by clause
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function orderby($column, $direction=null)
    {
        // Validate input type and set internal property
        $pattern = "/^[a-zA-Z_\#\.]+$/";
        $this->_orderby = PHPFrame_Utils_Filter::validateRegExp($column, $pattern);
        
        if (!is_null($direction)) {
            $this->orderdir($direction);
        }
        
        return $this;
    }
    
    /**
     * Set order direction
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function orderdir($direction)
    {
        // Validate input type and set internal property
        $pattern = "/^(ASC|DESC)$/i";
        $this->_orderdir = PHPFrame_Utils_Filter::validateRegExp($direction, $pattern);
        
        return $this;
    }
    
    /**
     * Set limit clause
     * 
     * @param int $limit     The total number of entries we want to limit to
     * @param int $limistart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function limit($limit, $limistart=null)
    {
        // Validate input type and set internal property 
        $this->_limit = PHPFrame_Utils_Filter::validateInt($limit);
        
        if (!is_null($limistart)) {
            $this->limistart($limistart);
        }
        
        return $this;
    }
    
    /**
     * Set row number of first row in current page
     * 
     * @param int $limistart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function limistart($limistart)
    {
        // Validate input type and set internal property 
        $this->_limitstart = PHPFrame_Utils_Filter::validateInt($limistart);
 
        return $this;
    }
    
    /**
     * Set the value of a query parameter
     * 
     * @param string $key   The parameter marker
     * @param string $value The paramter value
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function params($key, $value)
    {
        $this->_params[$key] = $value;
        
        return $this;
    }
    
    /**
     * Get full SQL statement for this IdObject
     * 
     * @param bool $limit A flag to indicate whether or not we want to include
     *                    LIMIT clause.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getSQL($limit=true)
    {
        $sql = $this->_getSelectSQL();
        $sql .= "\n".$this->_getFromSQL();
        
        if ($this->_getJoinsSQL()) {
            $sql .= "\n".$this->_getJoinsSQL();
        }
        
        if ($this->_getWhereSQL()) {
            $sql .= "\n".$this->_getWhereSQL();
        }
        
        if ($this->_getGroupBySQL()) {
            $sql .= "\n".$this->_getGroupBySQL();
        }
        
        if ($this->_getOrderBySQL()) {
            $sql .= "\n".$this->_getOrderBySQL();
        }
        
        if ($this->_getLimitSQL() && $limit) {
            $sql .= "\n".$this->_getLimitSQL();
        }
        
        return $sql;
    }
    
    /**
     * Get the an array with the fields in the SELECT query
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getFields()
    {
        return $this->_select;
    }
    
    /**
     * Get the table name in the FROM part of the query
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTableName()
    {
        if (is_array($this->_from)) {
            return $this->_from[0];
        }
        
        return $this->_from;
    }
    
    public function getJoinTables()
    {
        $join_tables = array();
        
        foreach ($this->_join as $join) {
            $join_tables[] = $join["table_name"];
        }
        
        return $join_tables;
    }
    
    /**
     * Get query parameters
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Get limit
     * 
     * The total number of entries the subset will be limited to.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimit()
    {
        return $this->_limit;
    }
    
    /**
     * Get limit start
     * 
     * The entry number of the first record in the current subset/page.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimitstart()
    {
        return $this->_limitstart;
    }
    
    /**
     * Get SELECT SQL
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getSelectSQL()
    {
        if (count($this->_select) < 1) {
            $exception_msg = "Can not build query. No fields have been selected.";
            throw new PHPFrame_Exception_Database($exception_msg);
        }
        
        $sql = "SELECT ";
        $sql .= implode(", ", $this->_select);
        
        return $sql;
    }
    
    /**
     * Get FROM SQL
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getFromSQL()
    {
        if (empty($this->_from)) {
            $exception_msg = "Can not build query. No table to select from.";
            throw new PHPFrame_Exception_Database($exception_msg);
        }
        
        if (is_array($this->_from)) {
            $sql = "FROM ".implode(" AS ", $this->_from);
        } else {
            $sql = "FROM ".$this->_from;
        }
        
        return $sql;
    }
    
    /**
     * 
     * Get JOIN SQL
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getJoinsSQL()
    {
        $sql = "";
        
        foreach ($this->_join as $join) {
            $sql .= " ".$join["type"]." ".$join["table_name"]." ".$join["table_alias"]." ON ";
            $sql .= $join["on"][0]." ".$join["on"][1]." ".$join["on"][2];
        }
        
        return $sql;
    }
    
    /**
     * Get WHERE SQL
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getWhereSQL()
    {
        $sql = "";
        
        if (count($this->_where) > 0) {
            $sql .= "WHERE ";
            
            for ($i=0; $i<count($this->_where); $i++) {
                if ($i>0) {
                    $sql .= " AND ";
                }
                $sql .= "(".$this->_where[$i][0];
                $sql .= " ".$this->_where[$i][1];
                $sql .= " ".$this->_where[$i][2].")";
            }
        }
        
        return $sql;
    }
    
    /**
     * Get GROUP BY SQL
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getGroupBySQL()
    {
        $sql = "";
        
        if (!empty($this->_groupby)) {
            $sql = "GROUP BY ".$this->_groupby;   
        }
        
        return $sql;
    }
    
    /**
     * Get ORDER BY SQL statement
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getOrderBySQL() 
    {
        $sql = "";
        
        if (is_string($this->_orderby) && $this->_orderby != "") {
            $sql .= " ORDER BY ".$this->_orderby." ";
            $sql .= ($this->_orderdir == "DESC") ? $this->_orderdir : "ASC";
        }
        
        return $sql;
    }
    
    /**
     * Get LIMIT SQL statement
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getLimitSQL() 
    {
        $sql = "";
        
        if ($this->_limit > 0) {
            $sql .= "LIMIT ".$this->_limitstart.", ".$this->_limit;
        }
        
        return $sql;
    }
}