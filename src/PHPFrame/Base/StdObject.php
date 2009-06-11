<?php
/**
 * PHPFrame/Base/StdObject.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Standard Object Class
 * 
 * This class provides a standard object with some useful generic methods.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Base_StdObject
{
    /**
     * Get property
     * 
     * @param    string    $property    The propery name to get
     * @param    string    $value        Default value if not defined
     * @return mixed
     * @since    1.0
     */
    function get($property, $value=null) {
        if (!$this->$property && $value) {
            $this->$property = $value;
        }
        return $this->$property;
    }
    
    /**
     * Set property
     * 
     * Sets the named property to th given value and returns the new value stored in the property.
     * 
     * @param    string    $property     The name of the property to set.
     * @param    mixed    $value         The new value for the property.
     * @return    mixed
     * @since    1.0
     */
    function set($property, $value) {
        $this->$property = $value;
        return $this->$property;
    }
    
    /**
     * Generates a storable string representation of the object
     * 
     * @return    string
     * @since    1.0
     */
    function toString() {
        return serialize($this);
    }
}
