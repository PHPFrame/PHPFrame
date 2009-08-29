<?php
/**
 * PHPFrame/Base/Array.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Base
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Array Class
 * 
 * @category PHPFrame
 * @package  Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Base_Array implements ArrayAccess, Countable, Iterator
{
    /**
     * Private property holding the array data
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Constructor
     * 
     * @param array $array
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $array=null)
    {
        if (!is_null($array)) {
            $this->_array = $array;
        }
    }
    
    /*
     * Array Access interface implementation (offsetExists(), offsetGet(), 
     * offsetSet() and offsetUnset())
     */
    
    /**
     * Check whether a given offset exists in uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_array);
    }
    
    /**
     * Get a given offset from uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function offsetGet($offset)
    {
        $offset = (string) trim($offset);
        
        return $this->_array[$offset];
    }

    /**
     * Set a given offset in uderlying array
     * 
     * @param string $offset
     * @param mixed $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function offsetSet($offset, $value)
    {
        $offset = (string) trim($offset);
        
        $this->_array[$offset] = $value;
    }

    /**
     * Unset a given offset from in uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function offsetUnset($offset)
    {
        unset($this->_array[$offset]);
    }
    
    /* 
     * Countable interface implementation 
     */
    
    /**
     * Count
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function count()
    {
        return count($this->_array);
    }
    
    /* 
     * Iterator interface implementation 
     */
    
    /**
     * Get element at current pointer position
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function current()
    {
        return $this->_array[$this->key()];
    }

    /**
     * Move pointer one step forward
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
     * Get current key at pointer position
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function key()
    {
        $keys = array_keys($this->_array);
        $key = $keys[$this->_pointer];
        
        return $key;
    }
    
    /**
     * Rewind internal pointer
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Check whether the current pointer position is valid
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function valid()
    {
        return ($this->_pointer < $this->count());
    }
}
