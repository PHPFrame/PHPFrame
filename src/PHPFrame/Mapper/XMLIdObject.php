<?php
/**
 * PHPFrame/Mapper/IdObject.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since     1.0
 */

/**
 * Identity Object class for XML implementation
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @todo     This class still needs to be implemented
 * @ignore
 */
class PHPFrame_XMLIdObject extends PHPFrame_IdObject
{
    /**
     * Constructor
     * 
     * @param array $options An associative array with initialisation options.
     *                       For a list of available options invoke 
     *                       PHPFrame_IdObject::getOptions().
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($options=null)
    {
        throw new LogicException("This class has not been implemented yet!");
        
        parent::__construct($options);
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
        return "";
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
        parent::getOptions();
    }
    
    /**
     * Set the fields array used in select statement
     * 
     * @param string|array $fields a string or array of strings with field names
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function select($fields) {}
    
    /**
     * Set the table from which to select rows
     * 
     * @param string $table A string with the table name
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function from($table) {}
    
    /**
     * Add "where" condition
     * 
     * @param string $left
     * @param string $operator
     * @param string $right
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function where($left, $operator, $right) {}
    
    /**
     * Get the an array with the fields in the SELECT query
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getObjectFields() {}
    
    /**
     * Get the table name in the FROM part of the query
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTableName() {}
}
