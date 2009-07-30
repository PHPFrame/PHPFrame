<?php
/**
 * PHPFrame/Mapper/Collection.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Collection Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Mapper_Collection implements Iterator
{
    private $_obj_fact;
    private $_total;
    private $_raw;
    private $_result;
    private $_pointer=0;
    private $_objects=array();
    
    public function __construct(
        array $raw=null, 
        PHPFrame_Mapper_DomainObjectFactory $obj_factory=null
    ) {
        if (!is_null($raw) && !is_null($obj_factory)) {
            $this->_raw = $raw;
            $this->_total = count($raw);
        }
        
        $this->_obj_fact = $obj_factory;
    }
    
    public function getElement($key)
    {
        if ($key >= $this->_total || $key < 0) {
            return null;   
        }
        
        if (isset($this->_objects[$key])) {
            return $this->_objects[$key];
        }
        
        if (isset($this->_raw[$key])) {
            $this->_objects[$key] = $this->_obj_fact->createObject($this->_raw[$key]);
            return $this->_objects[$key];
        }
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
        return $this->getElement($this->_pointer);
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
        $this->_pointer++;
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
        return $this->_pointer;
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
        return ($this->key() < $this->_total);
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
        $this->_pointer = 0;
    }
}
