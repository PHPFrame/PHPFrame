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
 * This class provides a basic array object that extends SPL's ArrayObject.
 * 
 * Array objects behave like arrays so they can be iterated using the foreach 
 * construct and also accessed using array sytax.
 * 
 * This class adds two methods on top of the SPL ArrayObject:
 * 
 *  - {@link PHPFrame_Array::isAssoc()}
 *  - {@link PHPFrame_Array::depth()}
 * 
 * @category PHPFrame
 * @package  Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      ArrayObject
 * @since    1.0
 */
class PHPFrame_Array extends ArrayObject
{
    /**
     * Private property holding the array data
     * 
     * @var array
     */
    private $_array = array();
    
    /**
     * Constructor
     * 
     * @param array $array [Optional] The array the object will represent.
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
        
        parent::__construct($this->_array);
    }
    
    /**
     * Magic method called we try to use an array object as a string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return print_r($this, true);
    }
    
    /**
     * Is associative array?
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isAssoc()
    {
        $keys_keys  = array_keys(array_keys($this->_array));
        $diff_count = count(array_diff_key($this->_array, $keys_keys));
        
        return (is_array($this->_array) && 0 !== $diff_count);
    }
    
    /**
     * Get array depth
     * 
     * @param array $array [Optional] The array to calculate the depth for. If
     *                     not passed the internal array is used.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function depth(array $array=null)
    {
        if (is_null($array)) {
            $array = $this->_array;
        }
        
        $depth = count($array) > 0 ? 1 : 0; 
        
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->depth($value) + 1;
            }
        }
        
        return (int) $depth;
    }
}
