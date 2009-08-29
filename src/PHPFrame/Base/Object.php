<?php
/**
 * PHPFrame/Base/StdObject.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Base
 * @author   Luis Montero <luis.montero@e-noise.com>
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
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Base_Object
{
    /**
     * __call() is triggered when invoking inaccessible methods in an object context. 
     * 
     * @param string $name
     * @param array  $arguments
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function __call($name, array $arguments)
    {
        
    }
    
    /**
     * __callStatic() is triggered when invoking inaccessible methods in a static context.
     * 
     * @param string $name
     * @param array  $arguments
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function __callstatic($name, array $arguments)
    {
        
    }
    
    /**
     * Clone
     * 
     * @access public
     * @return PHPFrame_Base_Object
     * @since  1.0
     */
    public function __clone()
    {
        
    }
    
    /**
     * Destructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        
    }
    
    /**
     * __get() is utilized for reading data from inaccessible members. 
     * 
     * @param string $name
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function __get($name)
    {
        // Check for accessor method following coding standards
        $reflectionObj = new ReflectionClass($this);
        
        // Build string with getter name
        $accessor_name = "get".ucwords(str_replace("_", " ", $name));
        $accessor_name = str_replace(" ", "", $accessor_name);
        
        if ($reflectionObj->hasMethod($accessor_name)) {
            $accessor_method = $reflectionObj->getMethod($accessor_name);
            $param_count = $accessor_method->getNumberOfParameters();
            if ($accessor_method->isPublic() && $param_count === 0) {
                return $this->$accessor_name();
            }
        }
        
        // Check for property with given name
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        
        // If we havent been able to get a value yet we throw an exception
        $msg = "Property '".$name."' doesn't exist in class ".get_class($this);
        throw new PHPFrame_Exception($msg);
    }
    
    /**
     * __isset() is triggered by calling isset() or empty()  on inaccessible members. 
     * 
     * @param string $name
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function __isset($name)
    {
        
    }
    
    /**
     * __set() is run when writing data to inaccessible members. 
     * 
     * @param string $name
     * @param mixed  $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __set($name, $value)
    {
        $msg = "Property does not exist";
        throw new PHPFrame_Exception($msg);
    }
    
    /**
     * This static method is called for classes exported by var_export() since PHP 5.1.0.
     *  
     * The only parameter of this method is an array containing exported properties in 
     * the form array('property' => value, ...).
     * 
     * @param array $properties
     * 
     * @access public
     * @return PHPFrame_Base_Object
     * @since  1.0
     */
    public static function __set_state(array $properties)
    {
        $obj = new self;
        
        foreach ($properties as $key=>$value) {
            $obj->$key = $value;
        }
        
        return $obj;
    }
    
    /**
     * The __toString method allows a class to decide how it will react when it is 
     * converted to a string.
     * 
     * This method only shows public and protected properties.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        $reflectionObj = new ReflectionClass($this);
        $properties = $reflectionObj->getProperties();
        
        foreach ($properties as $property) {
            if ($property->isPublic() || $property->isProtected()) {
                $property_name = $property->getName();
                $property_value = $this->$property_name;
                $str .= $property_name." => ".$property_value."\n";
            }
        }
        
        return $str;
    }
    
    /**
     * __unset() is invoked when unset() is used on inaccessible members. 
     * 
     * @param string $name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __unset($name)
    {
        
    }
}
