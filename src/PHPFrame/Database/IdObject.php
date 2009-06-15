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
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Database_IdObject
{
    /**
     * An array with the columns to get in SELECT statement
     * 
     * @var array
     */
    private $_fields=array();
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
    private $_joins=array();
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
    private $_group_by=null;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        //TODO
        // Constructor should allow to set properties 
        // ...
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
     * Set the fields array used in select statement
     * 
     * @param string|array $fields a string or array of strings with field names
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function select($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        
        $this->_fields = $fields;
        
        return $this;
    }
    
    /**
     * Set the table from which to select rows
     * 
     * @param string $str A string with the table name
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function from($str)
    {
        $this->_from = $str;
        
        return $this;
    }
    
    /**
     * Add a join clause to the select statement
     * 
     * @param sting $str A join statement
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function join($str)
    {
        $this->_joins[] = $str;
        
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
        $this->_where[] = array($left, $operator, $right);
        
        return $this;
    }
    
    /**
     * Set group by clause
     * 
     * @param string $str The column name to group by
     * 
     * @access public
     * @return PHPFrame_Database_IdObject
     * @since  1.0
     */
    public function groupBy($str)
    {
        $this->_group_by = $str;
        
        return $this;
    }
    
    /**
     * Get full SQL statement for this IdObject
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getSQL()
    {
        $sql = $this->_getSelectSQL();
        $sql .= "\n".$this->_getFromSQL();
        $sql .= "\n".$this->_getJoinsSQL();
        $sql .= "\n".$this->_getWhereSQL();
        $sql .= "\n".$this->_getGroupBySQL();
        
        return $sql;
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
        
        $sql = "SELECT ";
        $sql .= implode(", ", $this->_fields);
        
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
        $sql = " FROM ".$this->_from;
        
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
        $sql = " ";
        $sql .= implode(" ", $this->_joins);
        
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
        $sql = " WHERE ";
        
        for ($i=0; $i<count($this->_where); $i++) {
            if ($i>0) {
                $sql .= " AND ";
            }
            $sql .= $this->_where[$i][0];
            $sql .= " ".$this->_where[$i][1];
            $sql .= " ".$this->_where[$i][2];
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
        $sql = " GROUP BY ".$this->_group_by;
        
        return $sql;
    }
}