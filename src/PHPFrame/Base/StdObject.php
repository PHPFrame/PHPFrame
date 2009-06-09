<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    base
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Standard Object Class
 * 
 * This class provides a standard object class with some useful generic methods. 
 * 
 * @package        PHPFrame
 * @subpackage     base
 * @since         1.0
 */
class PHPFrame_Base_StdObject {
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
?>