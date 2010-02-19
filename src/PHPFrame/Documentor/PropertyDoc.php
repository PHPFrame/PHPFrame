<?php
/**
 * PHPFrame/Documentor/PropertyDoc.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Documentor
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Property Documentor Class
 * 
 * @category PHPFrame
 * @package  Documentor
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_PropertyDoc implements IteratorAggregate
{
    private $_array = array(
        "name"            => null, 
        "access"          => null,
        "type"            => null, 
        "declaring_class" => null
    );
    
    /**
     * Constructor.
     * 
     * @param ReflectionProperty $reflection_prop An object instance of 
     *                                            ReflectionProperty.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(ReflectionProperty $reflection_prop)
    {
        $this->_array["name"] = $reflection_prop->getName();
        
        if ($reflection_prop->isPublic()) {
            $this->_array["access"] = "public";
        } elseif ($reflection_prop->isProtected()) {
            $this->_array["access"] = "protected";
        } elseif ($reflection_prop->isPrivate()) {
            $this->_array["access"] = "private";
        }
        
        $declaring_class = $reflection_prop->getDeclaringClass();
        $this->_array["declaring_class"] = $declaring_class->getName();
    }
    
    /**
     * Convert object to string.
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str  = $this->_array["name"]." (".$this->_array["access"].") - ";
        $str .= $this->_array["declaring_class"]."\n";
        
        return $str;
    }
    
    /**
     * Implementation of the IteratorAggregate interface.
     * 
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
}
